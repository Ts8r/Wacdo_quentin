const CATEGORY_DETAILS = {
    menus: {
        label: "Menus",
        title: "Nos menus",
        description: "Un sandwich, une friture ou une salade et une boisson"
    },
    burgers: {
        label: "Sandwiches",
        title: "Nos Sandwiches",
        description: "Les classiques Wacdo préparés à la commande"
    },
    wraps: {
        label: "Wraps",
        title: "Nos Wraps",
        description: "Du poulet, du fromage et des recettes faciles à emporter"
    },
    frites: {
        label: "Frites",
        title: "Nos accompagnements",
        description: "Frites, potatoes, la pomme de terre dans tous ses états"
    },
    boissons: {
        label: "Boissons Froides",
        title: "Nos Boissons Fraîches",
        description: "Une petite soif, sucrée, légère, rafraîchissante"
    },
    encas: {
        label: "Encas",
        title: "Nos encas",
        description: "Une petite faim à ajouter à votre commande"
    },
    desserts: {
        label: "Desserts",
        title: "Nos desserts",
        description: "Terminez votre commande sur une touche sucrée"
    }
};

const CATEGORY_ORDER = ["menus", "burgers", "wraps", "frites", "boissons", "encas", "desserts"];
const WACDO_ASSET_ROOT = "/wacdo";

const MENU_OPTION_GROUPS = {
    sizes: {
        container: "menu-size-options",
        stateKey: "menuSize",
        options: [
            { label: "Menu Maxi Best Of", image: "/wacdo/images/illustration-maxi-best-of.png" },
            { label: "Menu Best Of", image: "/wacdo/images/illustration-best-of.png" }
        ]
    },
    sides: {
        container: "side-options",
        stateKey: "side",
        options: [
            { label: "Frites", image: "/wacdo/frites/GRANDE_FRITE.png" },
            { label: "Potatoes", image: "/wacdo/frites/POTATOES.png" }
        ]
    },
    drinks: {
        container: "drink-options",
        stateKey: "menuDrink",
        options: [
            { label: "Eau" },
            { label: "Coca Cola" },
            { label: "Coca Sans Sucres" },
            { label: "Jus de Pommes Bio" },
            { label: "Ice Tea Citron" }
        ]
    }
};

const DRINK_SIZES = ["30Cl", "50Cl"];

const state = {
    activeCategory: "menus",
    mode: "Sur place",
    products: {},
    cart: [],
    selectedProduct: null,
    menuSize: "Menu Maxi Best Of",
    side: "Frites",
    menuDrink: "Coca Cola",
    drinkSize: "30Cl",
    drinkQuantity: 1
};

const dom = {
    screens: document.querySelectorAll(".screen"),
    tabs: document.querySelector("#category-tabs"),
    grid: document.querySelector("#product-grid"),
    title: document.querySelector("#category-title"),
    description: document.querySelector("#category-description"),
    cartList: document.querySelector("#cart-list"),
    total: document.querySelector("#cart-total"),
    ticketMode: document.querySelector("#ticket-mode"),
    drinkQuantity: document.querySelector("#drink-quantity")
};

init();

async function init() {
    state.products = await loadProducts();
    renderTabs();
    renderProducts();
    renderCart();
    bindEvents();
}

async function loadProducts() {
    try {
        const response = await fetch("/wacdo/produits.json");

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return normalizeProductCatalogue(await response.json());
    } catch (error) {
        dom.grid.innerHTML = `<p class="empty-state">Impossible de charger le catalogue.</p>`;
        return {};
    }
}

function normalizeProductCatalogue(catalogue) {
    return Object.fromEntries(
        Object.entries(catalogue).map(([categoryId, entries]) => [
            categoryId,
            entries.map((product) => ({
                id: product.id,
                name: product.nom,
                price: Number(product.prix),
                image: normalizeImagePath(product.image),
                isDrink: categoryId === "boissons"
            }))
        ])
    );
}

function normalizeImagePath(path) {
    return `${WACDO_ASSET_ROOT}${path}`
        .replace(".png.png", ".png")
        .replace(".jpg.png", ".png");
}

function bindEvents() {
    document.querySelectorAll("[data-mode]").forEach((button) => {
        button.addEventListener("click", () => {
            state.mode = button.dataset.mode;
            renderCart();
            showScreen("#order-screen");
        });
    });

    dom.tabs.addEventListener("click", handleCategoryClick);
    dom.grid.addEventListener("click", handleProductClick);
    document.querySelector("#menu-modal").addEventListener("click", handleMenuOptionClick);
    document.querySelector("#drink-modal").addEventListener("click", handleDrinkOptionClick);
    document.querySelector("#add-menu").addEventListener("click", addConfiguredMenu);
    document.querySelector("#add-drink").addEventListener("click", addConfiguredDrink);
    document.querySelector("#increase-drink").addEventListener("click", () => updateDrinkQuantity(1));
    document.querySelector("#decrease-drink").addEventListener("click", () => updateDrinkQuantity(-1));
    document.querySelector("#reset-order").addEventListener("click", resetOrder);
    document.querySelector("#pay-order").addEventListener("click", startPayment);
    document.querySelector("#save-table").addEventListener("click", finishOrder);
    document.querySelector("#new-order").addEventListener("click", resetOrder);

    document.querySelectorAll("[data-close-modal]").forEach((button) => {
        button.addEventListener("click", closeModals);
    });
}

function handleCategoryClick(event) {
    const button = event.target.closest("[data-category]");

    if (!button) {
        return;
    }

    state.activeCategory = button.dataset.category;
    renderTabs();
    renderProducts();
}

function handleProductClick(event) {
    const button = event.target.closest("[data-product]");

    if (!button) {
        return;
    }

    const product = state.products[state.activeCategory][Number(button.dataset.product)];

    if (state.activeCategory === "menus") {
        prepareMenuModal(product);
        return;
    }

    if (product.isDrink) {
        prepareDrinkModal(product);
        return;
    }

    addToCart(createCartEntry(product));
}

function handleMenuOptionClick(event) {
    const option = event.target.closest(".option-card");

    if (!option) {
        return;
    }

    const group = Object.values(MENU_OPTION_GROUPS).find((entry) => entry.container === option.parentElement.id);

    if (!group) {
        return;
    }

    state[group.stateKey] = option.dataset.value;
    selectOption(group.container, option.dataset.value);
}

function handleDrinkOptionClick(event) {
    const option = event.target.closest(".option-card");

    if (!option) {
        return;
    }

    state.drinkSize = option.dataset.value;
    selectOption("drink-size-options", state.drinkSize);
}

function renderTabs() {
    dom.tabs.innerHTML = getVisibleCategories().map((category) => `
        <button class="tab ${category.id === state.activeCategory ? "is-active" : ""}" type="button" data-category="${category.id}">
            ${category.label}
        </button>
    `).join("");
}

function renderProducts() {
    const category = CATEGORY_DETAILS[state.activeCategory];
    const products = state.products[state.activeCategory] ?? [];

    dom.title.textContent = category.title;
    dom.description.textContent = category.description;
    dom.grid.innerHTML = products.map((product, index) => `
        <button class="product-card" type="button" data-product="${index}">
            <img src="${product.image}" alt="">
            <strong>${product.name}</strong>
            <span class="price">${formatPrice(product.price)}</span>
        </button>
    `).join("");
}

function renderCart() {
    dom.ticketMode.textContent = `${state.mode} : 326`;

    if (state.cart.length === 0) {
        dom.cartList.innerHTML = `<li class="empty-cart">Votre commande est vide</li>`;
        dom.total.textContent = formatPrice(0);
        return;
    }

    dom.cartList.innerHTML = state.cart.map((entry) => `
        <li class="cart-item">
            <div class="cart-line">
                <span>${entry.quantity > 1 ? entry.quantity + " x " : ""}${entry.name}</span>
                <span>${formatPrice(entry.price * entry.quantity)}</span>
            </div>
            ${renderCartOptions(entry.options)}
        </li>
    `).join("");

    dom.total.textContent = formatPrice(getCartTotal());
}

function renderCartOptions(options) {
    if (options.length === 0) {
        return "";
    }

    return `<ul class="cart-options">${options.map((option) => `<li>${option}</li>`).join("")}</ul>`;
}

function getVisibleCategories() {
    return CATEGORY_ORDER
        .filter((id) => CATEGORY_DETAILS[id] && state.products[id])
        .map((id) => ({ id, ...CATEGORY_DETAILS[id] }));
}

function getCartTotal() {
    return state.cart.reduce((sum, entry) => sum + entry.price * entry.quantity, 0);
}

function addToCart(entry) {
    state.cart.push(entry);
    renderCart();
}

function createCartEntry(product, overrides = {}) {
    return {
        name: product.name,
        price: product.price,
        quantity: 1,
        options: [],
        ...overrides
    };
}

function prepareMenuModal(product) {
    state.selectedProduct = product;
    state.menuSize = "Menu Maxi Best Of";
    state.side = "Frites";
    state.menuDrink = "Coca Cola";

    Object.values(MENU_OPTION_GROUPS).forEach((group) => {
        renderOptionCards(group.container, group.options, state[group.stateKey]);
    });

    openModal("#menu-modal");
}

function prepareDrinkModal(product) {
    state.selectedProduct = product;
    state.drinkSize = "30Cl";
    state.drinkQuantity = 1;
    dom.drinkQuantity.textContent = state.drinkQuantity;
    renderOptionCards(
        "drink-size-options",
        DRINK_SIZES.map((label) => ({ label, image: product.image })),
        state.drinkSize
    );
    openModal("#drink-modal");
}

function renderOptionCards(containerId, options, selected) {
    const container = document.querySelector(`#${containerId}`);

    container.innerHTML = options.map((option) => `
        <button class="option-card ${option.label === selected ? "is-selected" : ""}" type="button" data-value="${option.label}">
            ${option.image ? `<img src="${option.image}" alt="">` : ""}
            <span>${option.label}</span>
        </button>
    `).join("");
}

function addConfiguredMenu() {
    const product = state.selectedProduct;
    const extra = state.menuSize === "Menu Maxi Best Of" ? 1.50 : 0;

    addToCart(createCartEntry(product, {
        name: `${state.menuSize} ${product.name.replace(/^Menu\s/, "")}`,
        price: product.price + extra,
        options: [state.side.toLowerCase(), state.menuDrink.toLowerCase(), "ketchup", "sauce deluxe"]
    }));
    closeModals();
}

function addConfiguredDrink() {
    const product = state.selectedProduct;
    const extra = state.drinkSize === "50Cl" ? 0.50 : 0;

    addToCart(createCartEntry(product, {
        price: product.price + extra,
        quantity: state.drinkQuantity,
        options: [state.drinkSize]
    }));
    closeModals();
}

function updateDrinkQuantity(step) {
    state.drinkQuantity = Math.max(1, state.drinkQuantity + step);
    dom.drinkQuantity.textContent = state.drinkQuantity;
}

function resetOrder() {
    state.cart = [];
    state.activeCategory = "menus";
    renderTabs();
    renderProducts();
    renderCart();
    showScreen("#welcome-screen");
}

function startPayment() {
    if (state.mode === "Sur place") {
        openModal("#table-modal");
        return;
    }

    showScreen("#thanks-screen");
}

function finishOrder() {
    closeModals();
    showScreen("#thanks-screen");
}

function selectOption(containerId, value) {
    document.querySelectorAll(`#${containerId} .option-card`).forEach((card) => {
        card.classList.toggle("is-selected", card.dataset.value === value);
    });
}

function showScreen(id) {
    dom.screens.forEach((screen) => screen.classList.remove("is-active"));
    document.querySelector(id).classList.add("is-active");
}

function openModal(id) {
    document.querySelector(id).classList.add("is-open");
}

function closeModals() {
    document.querySelectorAll(".modal").forEach((modal) => modal.classList.remove("is-open"));
}

function formatPrice(value) {
    return value.toLocaleString("fr-FR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "€";
}
