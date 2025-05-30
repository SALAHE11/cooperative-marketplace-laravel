
<div class="row">
    <!-- Product Info Column -->
    <div class="col-lg-8">
        <!-- Basic Info -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-primary mb-3">{{ $product->name }}</h4>
                <div class="product-status d-none" data-status="{{ $product->status }}"></div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Coopérative:</strong></td>
                                <td>{{ $product->cooperative->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Catégorie:</strong></td>
                                <td>{{ $product->category->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Prix:</strong></td>
                                <td><span class="fw-bold text-success">{{ number_format($product->price, 2) }} MAD</span></td>
                            </tr>
                            <tr>
                                <td><strong>Stock:</strong></td>
                                <td><span class="fw-bold">{{ $product->stock_quantity }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $product->status_badge }}">
                                        {{ $product->status_text }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Soumis le:</strong></td>
                                <td>{{ $product->submitted_at ? $product->submitted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            @if($product->reviewed_at)
                                <tr>
                                    <td><strong>Examiné le:</strong></td>
                                    <td>{{ $product->reviewed_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if($product->reviewedBy)
                                <tr>
                                    <td><strong>Examiné par:</strong></td>
                                    <td>{{ $product->reviewedBy->full_name }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="row mb-4">
            <div class="col-12">
                <h6><i class="fas fa-align-left me-2"></i>Description</h6>
                <div class="card">
                    <div class="card-body">
                        <p class="mb-0">{{ $product->description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes / Rejection Reason -->
        @if($product->rejection_reason || $product->admin_notes)
            <div class="row mb-4">
                <div class="col-12">
                    @if($product->rejection_reason)
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-times-circle me-2"></i>Raison du rejet</h6>
                            <p class="mb-0">{{ $product->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($product->admin_notes)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-sticky-note me-2"></i>Notes administrateur</h6>
                            <p class="mb-0">{{ $product->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Images Column -->
    <div class="col-lg-4">
        <h6><i class="fas fa-images me-2"></i>Images du Produit</h6>

        @if($product->images->count() > 0)
            <div class="image-gallery">
                @foreach($product->images as $image)
                    <div class="position-relative mb-2">
                        <img src="{{ $image->image_url }}"
                             class="gallery-image"
                             alt="Image produit"
                             onclick="showImageModal('{{ $image->image_url }}')">
                        @if($image->is_primary)
                            <span class="primary-image-badge">Principal</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                <p class="text-muted">Aucune image disponible</p>
            </div>
        @endif
    </div>
</div>
