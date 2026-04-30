const DETAILS_CATEGORIES = {
    menus: {
        libelle: "Menus",
        titre: "Nos menus",
        description: "Un sandwich, une friture ou une salade et une boisson"
    },
    burgers: {
        libelle: "Sandwiches",
        titre: "Nos Sandwiches",
        description: "Les classiques Wacdo préparés à la commande"
    },
    wraps: {
        libelle: "Wraps",
        titre: "Nos Wraps",
        description: "Du poulet, du fromage et des recettes faciles à emporter"
    },
    frites: {
        libelle: "Frites",
        titre: "Nos accompagnements",
        description: "Frites, potatoes, la pomme de terre dans tous ses états"
    },
    boissons: {
        libelle: "Boissons Froides",
        titre: "Nos Boissons Fraîches",
        description: "Une petite soif, sucrée, légère, rafraîchissante"
    },
    encas: {
        libelle: "Encas",
        titre: "Nos encas",
        description: "Une petite faim à ajouter à votre commande"
    },
    desserts: {
        libelle: "Desserts",
        titre: "Nos desserts",
        description: "Terminez votre commande sur une touche sucrée"
    }
};

const ORDRE_CATEGORIES = ["menus", "burgers", "wraps", "frites", "boissons", "encas", "desserts"];
const API_BASE = (window.API_BASE || document.querySelector('meta[name="api-base"]')?.content || "https://quentin-wacdo.stark.a3n.fr/api").replace(/\/$/, "");
const SOURCE_DONNEES = "api";
const URL_DONNEES = {
    static: "/wacdo/produits.json",
    api: `${API_BASE}/catalogue`
};

const GROUPES_OPTIONS_MENU = {
    sizes: {
        conteneur: "options-taille-menu",
        cleEtat: "tailleMenu",
        options: []
    },
    sides: {
        conteneur: "options-accompagnement",
        cleEtat: "accompagnement",
        options: [
            { libelle: "Frites", image: "/wacdo/frites/GRANDE_FRITE.png" },
            { libelle: "Potatoes", image: "/wacdo/frites/POTATOES.png" }
        ]
    },
    drinks: {
        conteneur: "options-boisson",
        cleEtat: "boissonMenu",
        options: [
            { libelle: "Eau" },
            { libelle: "Coca Cola" },
            { libelle: "Coca Sans Sucres" },
            { libelle: "Jus de Pommes Bio" },
            { libelle: "Ice Tea Citron" }
        ]
    }
};

const TAILLES_BOISSON = ["30Cl", "50Cl"];

const etat = {
    categorieActive: "menus",
    mode: "Sur place",
    produits: {},
    panier: [],
    produitSelectionne: null,
    commandeEnCours: false,
    commandeFinalisee: null,
    tailleMenu: "M",
    accompagnement: "Frites",
    boissonMenu: "Coca Cola",
    tailleBoisson: "30Cl",
    quantiteBoisson: 1
};

const elements = {
    ecrans: document.querySelectorAll(".ecran"),
    onglets: document.querySelector("#onglets-categories"),
    grille: document.querySelector("#grille-produits"),
    titre: document.querySelector("#titre-categorie"),
    description: document.querySelector("#description-categorie"),
    listePanier: document.querySelector("#liste-panier"),
    total: document.querySelector("#panier-total"),
    modeTicket: document.querySelector("#mode-ticket"),
    numeroCommande: document.querySelector("#numero-commande"),
    messageCommande: document.querySelector("#message-commande"),
    boutonPayer: document.querySelector("#payer-commande"),
    boutonTable: document.querySelector("#enregistrer-table"),
    ticketFinal: document.querySelector("#ticket-final"),
    totalFinal: document.querySelector("#total-final"),
    quantiteBoisson: document.querySelector("#quantite-boisson")
};

init();

async function init() {
    etat.produits = await chargerProduits();
    afficherOnglets();
    afficherProduits();
    afficherPanier();
    lierEvenements();
}

async function chargerProduits() {
    try {
        const reponse = await fetch(URL_DONNEES[SOURCE_DONNEES], {
            credentials: "include",
            headers: {
                Accept: "application/json"
            }
        });

        if (!reponse.ok) {
            throw new Error(`HTTP ${reponse.status}`);
        }

        const donnees = await reponse.json();

        return SOURCE_DONNEES === "api"
            ? normaliserCatalogueApi(donnees)
            : normaliserCatalogueProduits(donnees);
    } catch (error) {
        elements.grille.innerHTML = `<p class="etat-vide">Impossible de charger le catalogue.</p>`;
        return {};
    }
}

function normaliserCatalogueApi(catalogue) {
    const donnees = catalogue.data ?? catalogue;
    const produitsGroupes = {};

    (donnees.produits ?? []).forEach((produit) => {
        const categorie = produit.categorie ?? "encas";
        produitsGroupes[categorie] ??= [];
        produitsGroupes[categorie].push({
            id: produit.id,
            nom: produit.nom,
            prix: produit.prix_unitaire,
            image: produit.image,
        });
    });

    produitsGroupes.menus = (donnees.menus ?? []).map((menu) => ({
        id: menu.id,
        nom: menu.nom,
        prix: menu.prix,
        prixTailles: menu.prix_tailles,
        image: menu.image,
    }));

    return normaliserCatalogueProduits(produitsGroupes);
}

function normaliserCatalogueProduits(catalogue) {
    return Object.fromEntries(
        Object.entries(catalogue).map(([categoryId, entrees]) => [
            categoryId,
            entrees.map((produit) => ({
                id: produit.id,
                nom: produit.nom,
                prix: Number(produit.prix),
                prixTailles: produit.prixTailles ?? null,
                image: normaliserCheminImage(produit.image),
                estBoisson: categoryId === "boissons"
            }))
        ])
    );
}

function normaliserCheminImage(chemin) {
    if (typeof chemin === "string" && chemin.startsWith("data:")) {
        return chemin;
    }

    const cheminNormalise = String(chemin || "").replace(/^\/?wacdo\//, "/");

    return `/wacdo${cheminNormalise}`
        .replace(".png.png", ".png")
        .replace(".jpg.png", ".png");
}

function echapperHtml(valeur) {
    return String(valeur ?? "").replace(/[&<>"']/g, (caractere) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "'": "&#039;"
    }[caractere]));
}

function lierEvenements() {
    document.querySelectorAll("[data-mode]").forEach((bouton) => {
        bouton.addEventListener("click", () => {
            etat.mode = bouton.dataset.mode;
            afficherPanier();
            afficherEcran("#ecran-commande");
        });
    });

    elements.onglets.addEventListener("click", gererClicCategorie);
    elements.grille.addEventListener("click", gererClicProduit);
    document.querySelector("#modale-menu").addEventListener("click", gererClicOptionMenu);
    document.querySelector("#modale-boisson").addEventListener("click", gererClicOptionBoisson);
    document.querySelector("#ajouter-menu").addEventListener("click", ajouterMenuConfigure);
    document.querySelector("#ajouter-boisson").addEventListener("click", ajouterBoissonConfigure);
    document.querySelector("#augmenter-boisson").addEventListener("click", () => modifierQuantiteBoisson(1));
    document.querySelector("#diminuer-boisson").addEventListener("click", () => modifierQuantiteBoisson(-1));
    document.querySelector("#abandon-commande").addEventListener("click", reinitialiserCommande);
    document.querySelector("#payer-commande").addEventListener("click", commencerPaiement);
    document.querySelector("#enregistrer-table").addEventListener("click", terminerCommande);
    document.querySelector("#nouvelle-commande").addEventListener("click", reinitialiserCommande);

    document.querySelectorAll("[data-fermer-modale]").forEach((bouton) => {
        bouton.addEventListener("click", fermerModales);
    });
}

function gererClicCategorie(evenement) {
    const bouton = evenement.target.closest("[data-categorie]");

    if (!bouton) {
        return;
    }

    etat.categorieActive = bouton.dataset.categorie;
    afficherOnglets();
    afficherProduits();
}

function gererClicProduit(evenement) {
    const bouton = evenement.target.closest("[data-produit]");

    if (!bouton) {
        return;
    }

    const produit = etat.produits[etat.categorieActive][Number(bouton.dataset.produit)];

    if (etat.categorieActive === "menus") {
        preparerModaleMenu(produit);
        return;
    }

    if (produit.estBoisson) {
        preparerModaleBoisson(produit);
        return;
    }

    ajouterAuPanier(creerLignePanier(produit));
}

function gererClicOptionMenu(evenement) {
    const option = evenement.target.closest(".carte-option");

    if (!option) {
        return;
    }

    const groupe = Object.values(GROUPES_OPTIONS_MENU).find((ligne) => ligne.conteneur === option.parentElement.id);

    if (!groupe) {
        return;
    }

    etat[groupe.cleEtat] = option.dataset.valeur;
    selectionnerOption(groupe.conteneur, option.dataset.valeur);
}

function gererClicOptionBoisson(evenement) {
    const option = evenement.target.closest(".carte-option");

    if (!option) {
        return;
    }

    etat.tailleBoisson = option.dataset.valeur;
    selectionnerOption("options-taille-boisson", etat.tailleBoisson);
}

function afficherOnglets() {
    elements.onglets.innerHTML = categoriesVisibles().map((categorie) => `
        <button class="onglet ${categorie.id === etat.categorieActive ? "est-actif" : ""}" type="button" data-categorie="${categorie.id}">
            ${categorie.libelle}
        </button>
    `).join("");
}

function afficherProduits() {
    const categorie = DETAILS_CATEGORIES[etat.categorieActive];
    const produits = etat.produits[etat.categorieActive] ?? [];

    elements.titre.textContent = categorie.titre;
    elements.description.textContent = categorie.description;
    elements.grille.innerHTML = produits.map((produit, index) => `
        <button class="carte-produit" type="button" data-produit="${index}">
            <img src="${echapperHtml(produit.image)}" alt="">
            <strong>${echapperHtml(produit.nom)}</strong>
            <span class="prix">${formaterPrix(produit.prix)}</span>
        </button>
    `).join("");
}

function afficherPanier() {
    elements.modeTicket.textContent = etat.mode;

    if (etat.panier.length === 0) {
        elements.listePanier.innerHTML = `<li class="panier-vide">Votre commande est vide</li>`;
        elements.total.textContent = formaterPrix(0);
        return;
    }

    elements.listePanier.innerHTML = etat.panier.map((ligne) => `
        <li class="element-panier">
            <div class="ligne-panier">
                <span>${ligne.quantite > 1 ? ligne.quantite + " x " : ""}${echapperHtml(ligne.nom)}</span>
                <span>${formaterPrix(ligne.prix * ligne.quantite)}</span>
            </div>
            ${afficherOptionsPanier(ligne.options)}
        </li>
    `).join("");

    elements.total.textContent = formaterPrix(totalPanier());
}

function afficherOptionsPanier(options) {
    if (options.length === 0) {
        return "";
    }

    return `<ul class="options-panier">${options.map((option) => `<li>${echapperHtml(option)}</li>`).join("")}</ul>`;
}

function categoriesVisibles() {
    return ORDRE_CATEGORIES
        .filter((id) => DETAILS_CATEGORIES[id] && etat.produits[id])
        .map((id) => ({ id, ...DETAILS_CATEGORIES[id] }));
}

function totalPanier() {
    return etat.panier.reduce((somme, ligne) => somme + ligne.prix * ligne.quantite, 0);
}

function ajouterAuPanier(ligne) {
    etat.panier.push(ligne);
    effacerMessageCommande();
    afficherPanier();
}

function creerLignePanier(produit, surcharges = {}) {
    return {
        id: produit.id,
        type: "produits",
        nom: produit.nom,
        prix: produit.prix,
        quantite: 1,
        options: [],
        ...surcharges
    };
}

function preparerModaleMenu(produit) {
    etat.produitSelectionne = produit;
    etat.tailleMenu = "M";
    etat.accompagnement = "Frites";
    etat.boissonMenu = "Coca Cola";

    Object.values(GROUPES_OPTIONS_MENU).forEach((groupe) => {
        const options = groupe.cleEtat === "tailleMenu" ? optionsTaillesMenu(produit) : groupe.options;
        afficherCartesOptions(groupe.conteneur, options, etat[groupe.cleEtat]);
    });

    ouvrirModale("#modale-menu");
}

function optionsTaillesMenu(produit) {
    const prixTailles = prixTaillesMenu(produit);

    return [
        { libelle: "S", description: `Menu S - ${formaterPrix(prixTailles.S)}`, image: "/wacdo/images/illustration-best-of.png" },
        { libelle: "M", description: `Menu M - ${formaterPrix(prixTailles.M)}`, image: "/wacdo/images/illustration-best-of.png" },
        { libelle: "L", description: `Menu L - ${formaterPrix(prixTailles.L)}`, image: "/wacdo/images/illustration-maxi-best-of.png" }
    ];
}

function prixTaillesMenu(produit) {
    const prixM = Number(produit.prix);

    return {
        S: Number(produit.prixTailles?.S ?? Math.max(0.01, prixM - 1)),
        M: Number(produit.prixTailles?.M ?? prixM),
        L: Number(produit.prixTailles?.L ?? prixM + 1)
    };
}

function preparerModaleBoisson(produit) {
    etat.produitSelectionne = produit;
    etat.tailleBoisson = "30Cl";
    etat.quantiteBoisson = 1;
    elements.quantiteBoisson.textContent = etat.quantiteBoisson;
    afficherCartesOptions(
        "options-taille-boisson",
        TAILLES_BOISSON.map((libelle) => ({ libelle, image: produit.image })),
        etat.tailleBoisson
    );
    ouvrirModale("#modale-boisson");
}

function afficherCartesOptions(idConteneur, options, selection) {
    const conteneur = document.querySelector(`#${idConteneur}`);

    conteneur.innerHTML = options.map((option) => `
        <button class="carte-option ${option.libelle === selection ? "est-selectionne" : ""}" type="button" data-valeur="${option.libelle}">
            ${option.image ? `<img src="${echapperHtml(option.image)}" alt="">` : ""}
            <span>${echapperHtml(option.description || option.libelle)}</span>
        </button>
    `).join("");
}

function ajouterMenuConfigure() {
    const produit = etat.produitSelectionne;
    const prixMenu = prixTaillesMenu(produit)[etat.tailleMenu] ?? Number(produit.prix);

    ajouterAuPanier(creerLignePanier(produit, {
        type: "menus",
        nom: `Menu ${etat.tailleMenu} ${produit.nom.replace(/^Menu\s/, "")}`,
        prix: prixMenu,
        taille: etat.tailleMenu,
        options: [etat.accompagnement.toLowerCase(), etat.boissonMenu.toLowerCase(), "ketchup", "sauce deluxe"]
    }));
    fermerModales();
}

function ajouterBoissonConfigure() {
    const produit = etat.produitSelectionne;
    const supplement = etat.tailleBoisson === "50Cl" ? 0.50 : 0;

    ajouterAuPanier(creerLignePanier(produit, {
        prix: produit.prix + supplement,
        quantite: etat.quantiteBoisson,
        options: [etat.tailleBoisson]
    }));
    fermerModales();
}

function modifierQuantiteBoisson(step) {
    etat.quantiteBoisson = Math.max(1, etat.quantiteBoisson + step);
    elements.quantiteBoisson.textContent = etat.quantiteBoisson;
}

function reinitialiserCommande() {
    etat.panier = [];
    etat.commandeFinalisee = null;
    etat.categorieActive = "menus";
    elements.numeroCommande.textContent = "--";
    effacerMessageCommande();
    afficherOnglets();
    afficherProduits();
    afficherPanier();
    afficherEcran("#ecran-accueil");
}

async function commencerPaiement() {
    if (etat.panier.length === 0) {
        afficherMessageCommande("Ajoutez au moins un produit avant de payer.", "info");
        return;
    }

    if (etat.mode === "Sur place") {
        ouvrirModale("#modale-table");
        return;
    }

    await validerCommande();
}

async function terminerCommande() {
    await validerCommande();
}

async function validerCommande() {
    if (etat.commandeEnCours) {
        return;
    }

    etat.commandeEnCours = true;
    mettreAJourEtatEnvoi();
    effacerMessageCommande();

    try {
        const commande = await envoyerCommande();
        etat.commandeFinalisee = commande;
        afficherConfirmationCommande(commande);
        fermerModales();
        afficherEcran("#remerciement-ecran");
    } catch (error) {
        fermerModales();
        afficherMessageCommande(messageErreurCommande(error), "erreur");
    } finally {
        etat.commandeEnCours = false;
        mettreAJourEtatEnvoi();
    }
}

async function envoyerCommande() {
    const payload = {
        canal: "borne",
        produits: etat.panier
            .filter((ligne) => ligne.type === "produits")
            .map((ligne) => ({ id: ligne.id, quantite: ligne.quantite })),
        menus: etat.panier
            .filter((ligne) => ligne.type === "menus")
            .map((ligne) => ({ id: ligne.id, quantite: ligne.quantite, taille: ligne.taille || "M" }))
    };

    const reponse = await fetch(`${API_BASE}/commandes`, {
        method: "POST",
        credentials: "include",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    });

    if (!reponse.ok) {
        const erreur = await reponse.json().catch(() => ({}));
        throw new Error(erreur.message || `HTTP ${reponse.status}`);
    }

    const donnees = await reponse.json();

    return donnees.data ?? donnees;
}

function afficherConfirmationCommande(commande) {
    const ticket = commande.numero_ticket || "--";
    const total = Number(commande.total_ttc ?? totalPanier());

    elements.numeroCommande.textContent = ticket.slice(-3);
    elements.ticketFinal.textContent = ticket;
    elements.totalFinal.textContent = formaterPrix(total);
}

function afficherMessageCommande(message, type) {
    elements.messageCommande.textContent = message;
    elements.messageCommande.className = `message-commande est-visible est-${type}`;
}

function effacerMessageCommande() {
    elements.messageCommande.textContent = "";
    elements.messageCommande.className = "message-commande";
}

function mettreAJourEtatEnvoi() {
    elements.boutonPayer.disabled = etat.commandeEnCours;
    elements.boutonTable.disabled = etat.commandeEnCours;
    elements.boutonPayer.textContent = etat.commandeEnCours ? "Envoi..." : "Payer";
    elements.boutonTable.textContent = etat.commandeEnCours ? "Envoi..." : "Enregistrer le numéro";
}

function messageErreurCommande(error) {
    const message = String(error?.message || "");

    if (message.includes("stock insuffisant")) {
        return message.charAt(0).toUpperCase() + message.slice(1) + ".";
    }

    if (message.includes("Failed to fetch") || message.includes("NetworkError")) {
        return "Impossible de contacter le serveur. Réessayez dans un instant.";
    }

    if (message.startsWith("Validation failed")) {
        return "La commande n'a pas pu être validée. Vérifiez votre panier.";
    }

    return message || "La commande n'a pas pu être envoyée. Réessayez dans un instant.";
}

function selectionnerOption(idConteneur, valeur) {
    document.querySelectorAll(`#${idConteneur} .carte-option`).forEach((card) => {
        card.classList.toggle("est-selectionne", card.dataset.valeur === valeur);
    });
}

function afficherEcran(id) {
    elements.ecrans.forEach((ecran) => ecran.classList.remove("est-actif"));
    document.querySelector(id).classList.add("est-actif");
}

function ouvrirModale(id) {
    document.querySelector(id).classList.add("est-ouverte");
}

function fermerModales() {
    document.querySelectorAll(".modale").forEach((modale) => modale.classList.remove("est-ouverte"));
}

function formaterPrix(valeur) {
    return valeur.toLocaleString("fr-FR", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "€";
}
