{{-- Stock Alert Configuration Modals --}}

<!-- Stock Alert Configuration Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAlertModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    Configurer Seuil d'Alerte Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stockAlertForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Produit</label>
                        <input type="text" class="form-control" id="productName" readonly>
                        <input type="hidden" id="productId">
                    </div>
                    <div class="mb-3">
                        <label for="stockAlertThreshold" class="form-label">Seuil d'Alerte Stock</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="stockAlertThreshold"
                                   min="0" max="1000" required>
                            <span class="input-group-text">unités</span>
                        </div>
                        <div class="form-text">
                            Vous serez alerté quand le stock descend à ce niveau ou en dessous.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="saveStockAlert()">
                    <i class="fas fa-save me-1"></i>
                    Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Stock Alert Configuration Modal -->
<div class="modal fade" id="bulkStockAlertModal" tabindex="-1" aria-labelledby="bulkStockAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkStockAlertModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    Configuration Groupée des Alertes Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkStockAlertForm">
                    <div class="mb-3">
                        <label for="bulkThreshold" class="form-label">Nouveau Seuil d'Alerte</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="bulkThreshold"
                                   min="0" max="1000" value="5" required>
                            <span class="input-group-text">unités</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="applyTo" class="form-label">Appliquer à</label>
                        <select class="form-select" id="applyTo" required>
                            <option value="all">Tous les produits</option>
                            <option value="approved">Produits approuvés uniquement</option>
                            <option value="low_stock">Produits actuellement en stock faible</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information:</strong> Cette action modifiera le seuil d'alerte pour plusieurs produits à la fois.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="saveBulkStockAlert()">
                    <i class="fas fa-save me-1"></i>
                    Appliquer la Configuration
                </button>
            </div>
        </div>
    </div>
</div>
