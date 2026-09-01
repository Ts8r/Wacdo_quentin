const DETAILS_CATEGORIES = {
    menus: {
        libelle: "Menus",
        titre: "Nos menus",
        description: "Un sandwich, une friture ou une salade et une boisson",
        image: "/wacdo/categories/menus.png"
    },
    burgers: {
        libelle: "Burgers",
        titre: "Nos Burgers",
        description: "Préparés minute, juste pour vous",
        image: "/wacdo/categories/burgers.png"
    },
    wraps: {
        libelle: "Wraps",
        titre: "Nos Wraps",
        description: "À déguster sur le pouce",
        image: "/wacdo/categories/wraps.png"
    },
    frites: {
        libelle: "Frites",
        titre: "Nos Frites",
        description: "Croustillantes à souhait",
        image: "/wacdo/categories/frites.png"
    },
    boissons: {
        libelle: "Boissons",
        titre: "Nos Boissons Fraîches",
        description: "Une petite soif, sucrée, légère, rafraîchissante",
        image: "/wacdo/categories/boissons.png"
    },
    encas: {
        libelle: "En-cas",
        titre: "Nos En-cas",
        description: "Pour les petites faims",
        image: "/wacdo/categories/encas.png"
    },
    salades: {
        libelle: "Salades",
        titre: "Nos Salades",
        description: "Fraîcheur et équilibre",
        image: "/wacdo/categories/salades.png"
    },
    desserts: {
        libelle: "Desserts",
        titre: "Nos Desserts",
        description: "Une petite douceur pour finir",
        image: "/wacdo/categories/desserts.png"
    },
    sauces: {
        libelle: "Sauces",
        titre: "Nos Sauces",
        description: "Pour accompagner frites et nuggets",
        image: "/wacdo/categories/sauces.png"
    }
};

const ORDRE_CATEGORIES = ["menus", "boissons", "burgers", "frites", "encas", "wraps", "salades", "desserts", "sauces"];
const API_BASE = (window.API_BASE || document.querySelector('meta[name="api-base"]')?.content || "https://quentin-wacdo.stark.a3n.fr/api").replace(/\/$/, "");
const CHEMINS_API = {
    catalogue: `${API_BASE}/catalogue`,
    commandes: `${API_BASE}/commandes`
};
const SOURCE_DONNEES = "api";
const URL_DONNEES = {
    static: "/wacdo/produits.json",
    api: CHEMINS_API.catalogue
};

const etat = {
    categorieActive: "menus",
    mode: "sur_place",
    produits: {},
    panier: [],
    produitSelectionne: null,
    commandeEnCours: false,
    commandeFinalisee: null,
    tailleMenu: "M",
    quantiteBoisson: 1,
    etapeMenu: 1
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
    boutonCategoriesGauche: document.querySelector("#categories-gauche"),
    boutonCategoriesDroite: document.querySelector("#categories-droite"),
    boutonMenuRetour: document.querySelector("#menu-retour"),
    boutonMenuSuivant: document.querySelector("#menu-suivant"),
    ticketFinal: document.querySelector("#ticket-final"),
    totalFinal: document.querySelector("#total-final"),
    quantiteBoisson: document.querySelector("#quantite-boisson")
};

function ajusterEchelleBorne() {
    const application = document.querySelector(".application");
    if (!application) {
        return;
    }

    const viewport = window.visualViewport;
    const largeurVisible = viewport?.width || window.innerWidth;
    const hauteurVisible = viewport?.height || window.innerHeight;
    const gaucheVisible = viewport?.offsetLeft || 0;
    const hautVisible = viewport?.offsetTop || 0;
    const margeSecurite = 0.94;
    const scale = Math.min(largeurVisible / 1440, hauteurVisible / 1024) * margeSecurite;

    application.style.left = `${gaucheVisible + largeurVisible / 2}px`;
    application.style.top = `${hautVisible + hauteurVisible / 2}px`;
    application.style.transform = `translate(-50%, -50%) scale(${scale})`;
}

init();
window.addEventListener("resize", ajusterEchelleBorne);
window.visualViewport?.addEventListener("resize", ajusterEchelleBorne);
window.visualViewport?.addEventListener("scroll", ajusterEchelleBorne);

async function init() {
    ajusterEchelleBorne();
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
    elements.boutonMenuSuivant.addEventListener("click", avancerMenu);
    elements.boutonMenuRetour.addEventListener("click", reculerMenu);
    document.querySelector("#ajouter-boisson").addEventListener("click", ajouterBoissonConfigure);
    document.querySelector("#augmenter-boisson").addEventListener("click", () => modifierQuantiteBoisson(1));
    document.querySelector("#diminuer-boisson").addEventListener("click", () => modifierQuantiteBoisson(-1));
    document.querySelector("#abandon-commande").addEventListener("click", reinitialiserCommande);
    document.querySelector("#payer-commande").addEventListener("click", commencerPaiement);
    elements.boutonCategoriesGauche.addEventListener("click", () => elements.onglets.scrollBy({ left: -300, behavior: "smooth" }));
    elements.boutonCategoriesDroite.addEventListener("click", () => elements.onglets.scrollBy({ left: 300, behavior: "smooth" }));
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

    if (option.parentElement.id !== "options-taille-menu") {
        return;
    }

    etat.tailleMenu = option.dataset.valeur;
    selectionnerOption("options-taille-menu", etat.tailleMenu);
}

function afficherOnglets() {
    elements.onglets.innerHTML = categoriesVisibles().map((categorie) => `
        <button class="onglet ${categorie.id === etat.categorieActive ? "est-actif" : ""}" type="button" data-categorie="${categorie.id}">
            <img src="${echapperHtml(categorie.image)}" alt="">
            <span>${categorie.libelle}</span>
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
            <div class="image-produit"><img src="${echapperHtml(produit.image)}" alt=""></div>
            <div class="nom-produit">${echapperHtml(produit.nom)}</div>
            <div class="prix-produit">${formaterPrix(produit.prix)}</div>
        </button>
    `).join("");
}

function afficherPanier() {
    elements.modeTicket.textContent = libelleModeService(etat.mode);

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
            <button class="bouton-supprimer" type="button" data-supprimer-panier="${echapperHtml(ligne.cle)}" aria-label="Supprimer">
                <img src="/wacdo/images/supprimer.png" alt="">
            </button>
        </li>
    `).join("");
    document.querySelectorAll("[data-supprimer-panier]").forEach((button) => {
        button.addEventListener("click", () => {
            etat.panier = etat.panier.filter((ligne) => ligne.cle !== button.dataset.supprimerPanier);
            afficherPanier();
        });
    });

    elements.total.textContent = formaterPrix(totalPanier());
}

function libelleModeService(mode) {
    return mode === "a_emporter" ? "A emporter" : "Sur place";
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
    const cle = `${ligne.type}|${ligne.id}|${ligne.taille || ""}|${ligne.options.join(",")}`;
    const existante = etat.panier.find((item) => item.cle === cle);

    if (existante) {
        existante.quantite += ligne.quantite;
    } else {
        if (ligne.type === "menus") {
            etat.panier = etat.panier.filter((item) => item.type !== "menus" || item.id !== ligne.id);
        }
        etat.panier.push({ ...ligne, cle });
    }
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
    etat.tailleMenu = "L";
    etat.etapeMenu = 1;

    afficherCartesOptions("options-taille-menu", optionsTaillesMenu(produit), etat.tailleMenu);
    afficherEtapeMenu(1);
    ouvrirModale("#modale-menu");
}

function optionsTaillesMenu(produit) {
    const prixTailles = prixTaillesMenu(produit);

    return [
        { valeur: "L", libelle: "Menu Maxi Best Of", prix: prixTailles.L, image: produit.image },
        { valeur: "M", libelle: "Menu Best Of", prix: prixTailles.M, image: produit.image }
    ];
}

function afficherEtapeMenu(etape) {
    etat.etapeMenu = etape;
    document.querySelectorAll("#modale-menu .etape-menu").forEach((section) => {
        section.hidden = Number(section.dataset.etape) !== etape;
    });
    elements.boutonMenuRetour.hidden = true;
    elements.boutonMenuSuivant.textContent = "Ajouter à ma commande";
}

function avancerMenu() {
    ajouterMenuConfigure();
}

function reculerMenu() {
    if (etat.etapeMenu > 1) {
        afficherEtapeMenu(etat.etapeMenu - 1);
    }
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
    etat.quantiteBoisson = 1;
    elements.quantiteBoisson.textContent = etat.quantiteBoisson;
    ouvrirModale("#modale-boisson");
}

function afficherCartesOptions(idConteneur, options, selection) {
    const conteneur = document.querySelector(`#${idConteneur}`);

    conteneur.innerHTML = options.map((option) => `
        <button class="carte-option ${(option.valeur || option.libelle) === selection ? "est-selectionne" : ""}" type="button" data-valeur="${echapperHtml(option.valeur || option.libelle)}">
            ${option.image ? `<img src="${echapperHtml(option.image)}" alt="">` : ""}
            <span>${echapperHtml(option.description || option.libelle)}</span>
            ${option.prix !== undefined ? `<small>${formaterPrix(option.prix)}</small>` : ""}
        </button>
    `).join("");
}

function ajouterMenuConfigure() {
    const produit = etat.produitSelectionne;
    const prixMenu = prixTaillesMenu(produit)[etat.tailleMenu] ?? Number(produit.prix);

    ajouterAuPanier(creerLignePanier(produit, {
        type: "menus",
        nom: `${etat.tailleMenu === "L" ? "Menu Maxi Best Of" : "Menu Best Of"} ${produit.nom.replace(/^Menu\s/, "")}`,
        prix: prixMenu,
        taille: etat.tailleMenu,
        options: [`Taille ${etat.tailleMenu}`]
    }));
    fermerModales();
}

function ajouterBoissonConfigure() {
    const produit = etat.produitSelectionne;

    ajouterAuPanier(creerLignePanier(produit, {
        quantite: etat.quantiteBoisson
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
    etat.mode = "sur_place";
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

    const reponse = await fetch(CHEMINS_API.commandes, {
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
    elements.boutonPayer.textContent = etat.commandeEnCours ? "Envoi..." : "Payer";
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
