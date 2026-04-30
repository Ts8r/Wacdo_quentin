<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WACDO - Borne</title>
    <link rel="stylesheet" href="/assets/css/borne.css">
</head>
<body>
    <main class="app">
        <section class="screen welcome is-active" id="welcome-screen" aria-label="Accueil borne">
            <div class="choice-panel">
                <h1>Bonjour,</h1>
                <p>Souhaitez-vous consommer votre menu sur place ou préférez-vous l'emporter ?</p>
                <div class="choice-grid">
                    <button class="choice-card" type="button" data-mode="Sur place">
                        <img src="/wacdo/images/illustration-sur-place.png" alt="">
                        <span>Sur Place</span>
                    </button>
                    <button class="choice-card" type="button" data-mode="A emporter">
                        <img src="/wacdo/images/illustration-a-emporter.png" alt="">
                        <span>A Emporter</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="screen order-screen" id="order-screen" aria-label="Commande">
            <div class="menu-layout">
                <section class="catalogue">
                    <header class="topbar">
                        <a class="brand-link" href="/" aria-label="Retour à l'accueil Wacdo">
                            <img class="brand" src="/wacdo/images/logo.png" alt="Wacdo">
                        </a>
                        <nav class="tabs" id="category-tabs" aria-label="Catégories"></nav>
                    </header>
                    <h1 class="section-title" id="category-title">Nos menus</h1>
                    <p class="section-subtitle" id="category-description">Un sandwich, une friture ou une salade et une boisson</p>
                    <div class="products" id="product-grid"></div>
                </section>

                <aside class="cart" aria-label="Panier">
                    <div class="cart-head">
                        <div>
                            <h2>Commande numéro</h2>
                            <div class="ticket-mode" id="ticket-mode">Sur place : 326</div>
                        </div>
                        <div class="order-number">72</div>
                    </div>
                    <ul class="cart-list" id="cart-list"></ul>
                    <div>
                        <div class="total">
                            <div class="total-label">TOTAL<br>(ttc)</div>
                            <div class="total-price" id="cart-total">0,00€</div>
                        </div>
                        <div class="cart-actions">
                            <button class="btn danger" type="button" id="reset-order">Abandon</button>
                            <button class="btn" type="button" id="pay-order">Payer</button>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="screen thanks" id="thanks-screen" aria-label="Fin de commande">
            <div class="thanks-panel">
                <h1>Toute l'équipe vous remercie,</h1>
                <p>Et vous souhaite un bon appétit dans nos restaurants,</p>
                <p>A bientôt !</p>
                <button class="btn" type="button" id="new-order">Nouvelle commande</button>
            </div>
        </section>
    </main>

    <div class="modal" id="menu-modal" role="dialog" aria-modal="true" aria-labelledby="menu-title">
        <div class="modal-panel">
            <h2 id="menu-title">Une grosse faim ?</h2>
            <p>Le menu Maxi Best Of comprend un sandwich, une grande frite et une boisson 50 Cl</p>
            <div class="option-grid" id="menu-size-options"></div>
            <p>Choisissez votre accompagnement</p>
            <div class="option-grid" id="side-options"></div>
            <p>Choisissez votre boisson</p>
            <div class="option-grid" id="drink-options"></div>
            <div class="modal-actions">
                <button class="btn secondary" type="button" data-close-modal>Retour</button>
                <button class="btn" type="button" id="add-menu">Ajouter le menu à ma commande</button>
            </div>
        </div>
    </div>

    <div class="modal" id="drink-modal" role="dialog" aria-modal="true" aria-labelledby="drink-title">
        <div class="modal-panel">
            <h2 id="drink-title">Une petite soif ?</h2>
            <p>Choisissez la taille de votre boisson, +0.50€ pour le format 50 Cl</p>
            <div class="option-grid" id="drink-size-options"></div>
            <div class="stepper" aria-label="Quantité">
                <button type="button" id="decrease-drink">-</button>
                <span id="drink-quantity">1</span>
                <button type="button" id="increase-drink">+</button>
            </div>
            <div class="modal-actions">
                <button class="btn secondary" type="button" data-close-modal>Annuler</button>
                <button class="btn" type="button" id="add-drink">Ajouter a ma commande</button>
            </div>
        </div>
    </div>

    <div class="modal" id="table-modal" role="dialog" aria-modal="true" aria-labelledby="table-title">
        <div class="modal-panel">
            <h2 id="table-title">Pour être servis à table,</h2>
            <p>Récupérez un chevalet et indiquez ici le numéro inscrit dessus</p>
            <div class="table-number">
                <input inputmode="numeric" maxlength="1" value="2" aria-label="Premier chiffre">
                <input inputmode="numeric" maxlength="1" value="6" aria-label="Deuxième chiffre">
                <input inputmode="numeric" maxlength="1" value="1" aria-label="Troisième chiffre">
            </div>
            <div class="modal-actions">
                <button class="btn" type="button" id="save-table">Enregistrer le numéro</button>
            </div>
        </div>
    </div>

    <script src="/assets/js/borne.js"></script>
</body>
</html>
