<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WACDO - Back office</title>
    <link rel="stylesheet" href="/assets/css/back-office.css">
</head>
<body>
    <main class="bo-shell">
        <aside class="bo-sidebar">
            <a class="bo-brand" href="/" aria-label="Back office Wacdo">
                <img src="/wacdo/images/logo.png" alt="Wacdo">
                <span>Back office</span>
            </a>
            <nav class="bo-nav" aria-label="Sections">
                <button class="nav-item is-active" type="button" data-view="commandes">Commandes</button>
                <button class="nav-item" type="button" data-view="catalogue">Catalogue</button>
                <button class="nav-item" type="button" data-view="ingredients">Ingredients</button>
                <button class="nav-item" type="button" data-view="utilisateurs">Utilisateurs</button>
            </nav>
            <button class="logout-button" type="button" id="logout-button" style="display: none;">Deconnexion</button>
        </aside>

        <section class="bo-main">
            <header class="bo-topbar">
                <div>
                    <p class="eyebrow">WACDO admin</p>
                    <h1 id="view-title">Commandes</h1>
                </div>
                <div class="user-chip" id="user-chip">Session</div>
            </header>

            <section class="login-panel" id="login-panel" aria-label="Connexion back office">
                <form class="login-form" id="login-form">
                    <h2>Connexion</h2>
                    <label>
                        Email
                        <input type="email" name="email" autocomplete="username" required>
                    </label>
                    <label>
                        Mot de passe
                        <input type="password" name="mot_de_passe" autocomplete="current-password" required>
                    </label>
                    <button class="primary-button" type="submit">Se connecter</button>
                    <p class="form-message" id="login-message"></p>
                </form>
            </section>

            <section class="workspace is-hidden" id="workspace">
                <section class="view-panel is-active" id="view-commandes">
                    <div class="toolbar">
                        <select id="orders-status" aria-label="Filtre statut">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="en_preparation">En preparation</option>
                            <option value="prete">Prete</option>
                            <option value="servie">Servie</option>
                            <option value="annulee">Annulee</option>
                        </select>
                        <select id="orders-channel" aria-label="Filtre canal">
                            <option value="">Tous les canaux</option>
                            <option value="borne">Borne</option>
                            <option value="sur_place">Sur place</option>
                            <option value="a_emporter">A emporter</option>
                        </select>
                        <button class="secondary-button" type="button" id="refresh-orders">Actualiser</button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Date</th>
                                    <th>Canal</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Detail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="orders-body"></tbody>
                        </table>
                    </div>
                </section>

                <section class="view-panel" id="view-catalogue">
                    <div class="toolbar">
                        <input type="search" id="catalogue-search" placeholder="Rechercher un produit ou menu" aria-label="Recherche catalogue">
                        <button class="secondary-button" type="button" id="refresh-catalogue">Actualiser</button>
                    </div>
                    <div class="split-grid">
                        <section>
                            <h2>Produits</h2>
                            <div class="cards-grid" id="products-list"></div>
                        </section>
                        <section>
                            <h2>Menus</h2>
                            <div class="cards-grid" id="menus-list"></div>
                        </section>
                    </div>
                </section>

                <section class="view-panel" id="view-ingredients">
                    <div class="toolbar">
                        <input type="search" id="ingredients-search" placeholder="Rechercher un ingredient" aria-label="Recherche ingredient">
                        <button class="secondary-button" type="button" id="refresh-ingredients">Actualiser</button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ingredient</th>
                                    <th>Cout</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ingredients-body"></tbody>
                        </table>
                    </div>
                </section>

                <section class="view-panel" id="view-utilisateurs">
                    <div class="toolbar">
                        <input type="search" id="users-search" placeholder="Rechercher un utilisateur" aria-label="Recherche utilisateur">
                        <select id="users-role" aria-label="Filtre role">
                            <option value="">Tous les roles</option>
                            <option value="CLIENT">Client</option>
                            <option value="EMPLOYE">Employe</option>
                            <option value="MANAGER">Manager</option>
                            <option value="ADMIN">Admin</option>
                        </select>
                        <button class="secondary-button" type="button" id="refresh-users">Actualiser</button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
                                    <th>Role</th>
                                    <th>Creation</th>
                                </tr>
                            </thead>
                            <tbody id="users-body"></tbody>
                        </table>
                    </div>
                </section>
            </section>

            <div class="toast" id="toast" role="status" aria-live="polite"></div>
        </section>
    </main>

    <script src="/assets/js/back-office.js"></script>
</body>
</html>
