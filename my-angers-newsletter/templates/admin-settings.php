<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['man_save_settings'])) {
    update_option('man_double_optin', isset($_POST['double_optin']) ? '1' : '0');
    update_option('man_auto_notify', isset($_POST['auto_notify']) ? '1' : '0');
    echo '<div class="alert alert-success mt-3">Réglages enregistrés !</div>';
}

$double_optin = get_option('man_double_optin', '1');
$auto_notify = get_option('man_auto_notify', '0');
?>

<div class="wrap man-admin-wrap">
    <div class="container-fluid mt-4">
        <h1 class="display-6 fw-bold mb-4">Réglages de la Newsletter</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Général</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="double_optin" id="doubleOptin" <?php checked($double_optin, '1'); ?>>
                                    <label class="form-check-label" for="doubleOptin">Activer le Double Opt-in (Recommandé)</label>
                                    <div class="form-text">Envoie un email de confirmation avant d'ajouter l'abonné.</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_notify" id="autoNotify" <?php checked($auto_notify, '1'); ?>>
                                    <label class="form-check-label" for="autoNotify">Notification automatique de nouvel article</label>
                                    <div class="form-text">Envoie un email aux abonnés dès qu'une nouvelle publication est en ligne.</div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Identité</h5>
                                <div class="mb-3">
                                    <label class="form-label">Nom de l'expéditeur</label>
                                    <input type="text" class="form-control" value="Angers Info" disabled>
                                </div>
                            </div>

                            <button type="submit" name="man_save_settings" class="btn btn-primary">Enregistrer les réglages</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
