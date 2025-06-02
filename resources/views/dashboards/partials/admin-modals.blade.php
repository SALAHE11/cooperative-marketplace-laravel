{{-- Primary Admin Management Modals --}}

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Détails de la Demande d'Adhésion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-warning me-2" id="clarificationBtn">
                    <i class="fas fa-question-circle me-1"></i>
                    Demander Clarification
                </button>
                <button type="button" class="btn btn-danger me-2" id="rejectBtn">
                    <i class="fas fa-times me-1"></i>
                    Rejeter
                </button>
                <button type="button" class="btn btn-success" id="approveBtn">
                    <i class="fas fa-check me-1"></i>
                    Approuver
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">
                    <i class="fas fa-comment me-2"></i>
                    <span id="responseModalTitle">Message</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <div class="mb-3">
                        <label for="responseMessage" class="form-label" id="responseMessageLabel">Message</label>
                        <textarea class="form-control" id="responseMessage" name="response_message" rows="4" placeholder="Tapez votre message..."></textarea>
                        <div class="form-text" id="responseMessageHelp">Ce message sera envoyé par email au candidat.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="sendResponseBtn">
                    <i class="fas fa-paper-plane me-1"></i>
                    <span id="sendResponseText">Envoyer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Removal Confirmation Modal -->
<div class="modal fade" id="removeAdminModal" tabindex="-1" aria-labelledby="removeAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="removeAdminModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer le Retrait Temporaire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Retrait temporaire:</strong> Cette action peut être annulée depuis l'onglet "Administrateurs Inactifs".
                </div>
                <p>Êtes-vous sûr de vouloir retirer temporairement <strong id="adminToRemoveName"></strong> de l'administration de la coopérative?</p>

                <div class="mb-3">
                    <label for="removalReason" class="form-label">Motif du retrait (optionnel)</label>
                    <textarea class="form-control" id="removalReason" rows="3" placeholder="Expliquez la raison du retrait..."></textarea>
                    <small class="form-text text-muted">Ce motif sera inclus dans l'email de notification.</small>
                </div>

                <p class="text-muted small">L'administrateur sera notifié par email et pourra être réactivé ultérieurement.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="confirmRemoveBtn">
                    <i class="fas fa-user-minus me-1"></i>
                    Confirmer le Retrait
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Reactivation Modal -->
<div class="modal fade" id="reactivateAdminModal" tabindex="-1" aria-labelledby="reactivateAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="reactivateAdminModalLabel">
                    <i class="fas fa-user-check me-2"></i>
                    Confirmer la Réactivation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Réactivation:</strong> L'administrateur retrouvera tous ses droits d'accès.
                </div>
                <p>Êtes-vous sûr de vouloir réactiver <strong id="adminToReactivateName"></strong> comme administrateur de la coopérative?</p>

                <div class="mb-3">
                    <label for="reactivationMessage" class="form-label">Message de bienvenue (optionnel)</label>
                    <textarea class="form-control" id="reactivationMessage" rows="3" placeholder="Message de bienvenue pour le retour de l'administrateur..."></textarea>
                    <small class="form-text text-muted">Ce message sera inclus dans l'email de réactivation.</small>
                </div>

                <p class="text-muted small">L'administrateur sera notifié par email et pourra immédiatement accéder au tableau de bord.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="confirmReactivateBtn">
                    <i class="fas fa-user-check me-1"></i>
                    Confirmer la Réactivation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Permanent Removal Confirmation Modal -->
<div class="modal fade" id="permanentRemovalModal" tabindex="-1" aria-labelledby="permanentRemovalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="permanentRemovalModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer le Retrait Définitif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-warning me-2"></i>
                    <strong>Action irréversible!</strong> Cette action ne peut pas être annulée.
                </div>
                <p>Êtes-vous sûr de vouloir définitivement retirer <strong id="adminToPermanentlyRemoveName"></strong> de la coopérative?</p>

                <div class="alert alert-info">
                    <h6>Conséquences:</h6>
                    <ul class="mb-0">
                        <li>L'administrateur sera converti en client régulier</li>
                        <li>Tous les liens avec la coopérative seront supprimés</li>
                        <li>Cette action ne peut pas être annulée</li>
                        <li>Le compte utilisateur reste actif (pas supprimé)</li>
                    </ul>
                </div>

                <p class="text-muted small"><strong>Note:</strong> L'utilisateur pourra toujours utiliser son compte comme client ou rejoindre d'autres coopératives.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmPermanentRemovalBtn">
                    <i class="fas fa-user-times me-1"></i>
                    Retirer Définitivement
                </button>
            </div>
        </div>
    </div>
</div>
