<section class="titre">
    <h1>Connectez-vous pour vivre une expérience magique</h1>
</section>

<section class="container contenu">
    <div class="d-flex flex-row justify-content-around">

        <!--Formulaire de connexion -->
        <div class="connexion">
            <div>
                <h2>J'ai un compte</h2>
            </div>
            <div>
                <form method="POST" action="/login" class="d-flex flex-column">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-connect">
                        <button type="submit" class="btn-fond-fonce">Je me connecte</button>
                    </div>
                </form>
            </div>
        </div>

        <!--Séparation trait vertical -->
        <div class="auth-divider"></div>

        <!--Formulaire d'inscription -->
        <div class="inscription">
            <div>
                <h2>Je n'ai pas encore de compte</h2>
            </div>
            <div>
                <form method="POST" action="/register" class="d-flex flex-column">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">
                    <div class="form-group">
                        <label for="firstname">Prénom</label>
                        <input type="text" id="firstname" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Nom</label>
                        <input type="text" id="lastname" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                        <small>Votre mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.</small>
                    </div>
                    <div class="form-group">
                        <label for="password-confirm">Confirmer mon mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-connect">
                        <button type="submit" class="btn-fond-fonce">Je m'inscris</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
