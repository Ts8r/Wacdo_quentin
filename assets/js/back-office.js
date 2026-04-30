const state = {
    user: null,
    view: 'commandes',
    catalogue: { produits: [], menus: [] },
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => Array.from(document.querySelectorAll(selector));

const titles = {
    commandes: 'Commandes',
    catalogue: 'Catalogue',
    ingredients: 'Ingredients',
    utilisateurs: 'Utilisateurs',
};

const nextStatuses = {
    en_attente: ['en_preparation', 'annulee'],
    en_preparation: ['prete', 'annulee'],
    prete: ['servie', 'annulee'],
    servie: [],
    annulee: [],
};

function formatMoney(value) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(Number(value || 0));
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}

async function api(path, options = {}) {
    const response = await fetch(path, {
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
        ...options,
    });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || `Erreur HTTP ${response.status}`);
    }

    return payload;
}

function showToast(message) {
    const toast = $('#toast');
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => toast.classList.remove('is-visible'), 2800);
}

function setSession(user) {
    state.user = user;
    $('#login-panel').classList.toggle('is-hidden', Boolean(user));
    $('#workspace').classList.toggle('is-hidden', !user);
    $('#logout-button').style.display = user ? '' : 'none';
    $('#user-chip').textContent = user ? `${user.prenom} ${user.nom} - ${user.role.code}` : 'Session';

    if (user) {
        loadCurrentView();
    }
}

async function checkSession() {
    try {
        const payload = await api('/api/auth/me');
        setSession(payload.data.user);
    } catch (error) {
        setSession(null);
    }
}

async function login(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const message = $('#login-message');
    message.textContent = '';

    try {
        const payload = await api('/api/auth/login', {
            method: 'POST',
            body: JSON.stringify({
                email: form.email.value,
                mot_de_passe: form.mot_de_passe.value,
            }),
        });
        setSession(payload.data.user);
        showToast('Connexion reussie.');
    } catch (error) {
        message.textContent = error.message;
    }
}

async function logout() {
    await api('/api/auth/logout', { method: 'POST' });
    setSession(null);
}

function switchView(view) {
    state.view = view;
    $('#view-title').textContent = titles[view];
    $$('.nav-item').forEach((button) => button.classList.toggle('is-active', button.dataset.view === view));
    $$('.view-panel').forEach((panel) => panel.classList.toggle('is-active', panel.id === `view-${view}`));
    loadCurrentView();
}

function statusClass(status) {
    if (status === 'servie') {
        return 'is-ok';
    }
    if (status === 'en_attente' || status === 'en_preparation' || status === 'prete') {
        return 'is-warn';
    }
    return '';
}

async function loadOrders() {
    const params = new URLSearchParams({ limit: '30', offset: '0' });
    const status = $('#orders-status').value;
    const channel = $('#orders-channel').value;

    if (status) params.set('statut', status);
    if (channel) params.set('canal', channel);

    const payload = await api(`/api/commandes?${params}`);
    const body = $('#orders-body');
    body.innerHTML = '';

    payload.data.forEach((order) => {
        const status = order.statut.libelle;
        const options = nextStatuses[status] || [];
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(order.numero_ticket)}</td>
            <td>${escapeHtml(order.date_cmd)}</td>
            <td>${escapeHtml(order.canal.libelle)}</td>
            <td>${formatMoney(order.total_ttc)}</td>
            <td><span class="status-pill ${statusClass(status)}">${escapeHtml(status)}</span></td>
            <td></td>
        `;
        const actionCell = row.lastElementChild;

        if (options.length === 0) {
            actionCell.textContent = 'Aucune';
        } else {
            const select = document.createElement('select');
            select.setAttribute('aria-label', `Changer le statut ${order.numero_ticket}`);
            select.innerHTML = '<option value="">Changer</option>' + options.map((option) => `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`).join('');
            select.addEventListener('change', async () => {
                if (!select.value) return;
                await api(`/api/commandes/${order.id}/statut`, {
                    method: 'PATCH',
                    body: JSON.stringify({ statut: select.value }),
                });
                showToast('Statut mis a jour.');
                loadOrders();
            });
            actionCell.appendChild(select);
        }

        body.appendChild(row);
    });
}

async function loadCatalogue() {
    const payload = await api('/api/catalogue');
    state.catalogue = payload.data;
    renderCatalogue();
}

function renderCatalogue() {
    const filter = $('#catalogue-search').value.trim().toLowerCase();
    renderProductCards(state.catalogue.produits.filter((item) => item.nom.toLowerCase().includes(filter)));
    renderMenuCards(state.catalogue.menus.filter((item) => item.nom.toLowerCase().includes(filter)));
}

function renderProductCards(products) {
    const list = $('#products-list');
    list.innerHTML = '';

    products.forEach((product) => {
        const card = document.createElement('article');
        card.className = 'edit-card';
        card.innerHTML = `
            <img src="${escapeHtml(product.image || '/wacdo/images/logo.png')}" alt="">
            <div>
                <h3>${escapeHtml(product.nom)}</h3>
                <div class="edit-grid">
                    <input type="number" step="0.01" min="0" value="${Number(product.prix_unitaire).toFixed(2)}" aria-label="Prix">
                    <input type="number" step="1" min="0" value="${Number(product.quantite)}" aria-label="Quantite">
                    <label><input type="checkbox" ${Number(product.disponibilite) === 1 ? 'checked' : ''}> Disponible</label>
                    <button class="small-button" type="button">Enregistrer</button>
                </div>
            </div>
        `;
        const [priceInput, quantityInput] = card.querySelectorAll('input[type="number"]');
        const availableInput = card.querySelector('input[type="checkbox"]');
        card.querySelector('button').addEventListener('click', async () => {
            await api(`/api/produits/${product.id}`, {
                method: 'PATCH',
                body: JSON.stringify({
                    prix: Number(priceInput.value),
                    quantite: Number(quantityInput.value),
                    disponibilite: availableInput.checked,
                }),
            });
            showToast('Produit mis a jour.');
            loadCatalogue();
        });
        list.appendChild(card);
    });
}

function renderMenuCards(menus) {
    const list = $('#menus-list');
    list.innerHTML = '';

    menus.forEach((menu) => {
        const prices = menu.prix_tailles || {
            S: Math.max(0.01, Number(menu.prix) - 1),
            M: Number(menu.prix),
            L: Number(menu.prix) + 1,
        };
        const card = document.createElement('article');
        card.className = 'edit-card';
        card.innerHTML = `
            <img src="${escapeHtml(menu.image || '/wacdo/images/logo.png')}" alt="">
            <div>
                <h3>${escapeHtml(menu.nom)}</h3>
                <div class="edit-grid">
                    <input type="number" step="0.01" min="0.01" value="${Number(prices.S).toFixed(2)}" aria-label="Prix S" data-size-price="S">
                    <input type="number" step="0.01" min="0.01" value="${Number(prices.M).toFixed(2)}" aria-label="Prix M" data-size-price="M">
                    <input type="number" step="0.01" min="0.01" value="${Number(prices.L).toFixed(2)}" aria-label="Prix L" data-size-price="L">
                    <label><input type="checkbox" ${Number(menu.disponibilite) === 1 ? 'checked' : ''}> Disponible</label>
                    <button class="small-button" type="button">Enregistrer</button>
                </div>
            </div>
        `;
        const priceInputs = Array.from(card.querySelectorAll('[data-size-price]'));
        const availableInput = card.querySelector('input[type="checkbox"]');
        priceInputs.forEach((input) => {
            input.addEventListener('input', () => syncMenuSizePrices(input, priceInputs));
        });
        card.querySelector('button').addEventListener('click', async () => {
            const pricesBySize = Object.fromEntries(priceInputs.map((input) => [input.dataset.sizePrice, Number(input.value)]));
            await api(`/api/menus/${menu.id}`, {
                method: 'PATCH',
                body: JSON.stringify({
                    prix_s: pricesBySize.S,
                    prix_m: pricesBySize.M,
                    prix_l: pricesBySize.L,
                    disponibilite: availableInput.checked,
                }),
            });
            showToast('Menu mis a jour.');
            loadCatalogue();
        });
        list.appendChild(card);
    });
}

function syncMenuSizePrices(changedInput, inputs) {
    const size = changedInput.dataset.sizePrice;
    const value = Number(changedInput.value);

    if (!Number.isFinite(value) || value <= 0) {
        return;
    }

    const basePrice = size === 'S' ? value + 1 : size === 'L' ? value - 1 : value;
    const prices = {
        S: Math.max(0.01, basePrice - 1),
        M: basePrice,
        L: basePrice + 1,
    };

    inputs.forEach((input) => {
        if (input !== changedInput) {
            input.value = prices[input.dataset.sizePrice].toFixed(2);
        }
    });
}

async function loadIngredients() {
    const params = new URLSearchParams({ limit: '80', offset: '0' });
    const search = $('#ingredients-search').value.trim();
    if (search) params.set('search', search);

    const payload = await api(`/api/ingredients?${params}`);
    const body = $('#ingredients-body');
    body.innerHTML = '';

    payload.data.forEach((ingredient) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(ingredient.nom)}</td>
            <td>${formatMoney(ingredient.cout_unitaire)}</td>
            <td><input class="stock-input" type="number" min="0" step="1" value="${Number(ingredient.quantite)}" aria-label="Stock ${escapeHtml(ingredient.nom)}"></td>
            <td><button class="small-button" type="button">Enregistrer</button></td>
        `;
        const input = row.querySelector('input');
        row.querySelector('button').addEventListener('click', async () => {
            await api(`/api/ingredients/${ingredient.id}`, {
                method: 'PATCH',
                body: JSON.stringify({ quantite: Number(input.value) }),
            });
            showToast('Stock mis a jour.');
            loadIngredients();
        });
        body.appendChild(row);
    });
}

async function loadUsers() {
    const params = new URLSearchParams({ limit: '50', offset: '0' });
    const search = $('#users-search').value.trim();
    const role = $('#users-role').value;
    if (search) params.set('search', search);
    if (role) params.set('role', role);

    const payload = await api(`/api/utilisateurs?${params}`);
    const body = $('#users-body');
    body.innerHTML = '';

    payload.data.forEach((user) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(user.prenom)} ${escapeHtml(user.nom)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.num_tel || '-')}</td>
            <td><span class="status-pill">${escapeHtml(user.role.code)}</span></td>
            <td>${escapeHtml(user.created_at || '-')}</td>
        `;
        body.appendChild(row);
    });
}

async function loadCurrentView() {
    if (!state.user) return;

    try {
        if (state.view === 'commandes') await loadOrders();
        if (state.view === 'catalogue') await loadCatalogue();
        if (state.view === 'ingredients') await loadIngredients();
        if (state.view === 'utilisateurs') await loadUsers();
    } catch (error) {
        showToast(error.message);
    }
}

$('#login-form').addEventListener('submit', login);
$('#logout-button').addEventListener('click', logout);
$$('.nav-item').forEach((button) => button.addEventListener('click', () => switchView(button.dataset.view)));
$('#refresh-orders').addEventListener('click', loadOrders);
$('#orders-status').addEventListener('change', loadOrders);
$('#orders-channel').addEventListener('change', loadOrders);
$('#refresh-catalogue').addEventListener('click', loadCatalogue);
$('#catalogue-search').addEventListener('input', renderCatalogue);
$('#refresh-ingredients').addEventListener('click', loadIngredients);
$('#ingredients-search').addEventListener('input', () => window.clearTimeout(loadIngredients.timer) || (loadIngredients.timer = window.setTimeout(loadIngredients, 250)));
$('#refresh-users').addEventListener('click', loadUsers);
$('#users-search').addEventListener('input', () => window.clearTimeout(loadUsers.timer) || (loadUsers.timer = window.setTimeout(loadUsers, 250)));
$('#users-role').addEventListener('change', loadUsers);

checkSession();
