function createProductCard(product) {
    let stock = parseInt(product.stock_available || 0);
    let minStock = parseInt(product.min_stock || 0);
    let statusClass, statusText;

    if (stock <= 0) {
        statusClass = 'tag-stock-zero';
        statusText = 'Out of Stock';
    } else if (stock <= minStock) {
        statusClass = 'tag-stock-low';
        statusText = 'Low Stock';
    } else {
        statusClass = 'tag-stock-sufficient';
        statusText = 'Sufficient';
    }

    return `
        <div class="col-sm-6 col-lg-4 mb-4 product-card-wrapper">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-subtitle mb-2">
                        <span class="badge ${statusClass}">${statusText}</span>
                    </p>
                    <div class="mb-2">
                         <span class="badge bg-secondary">${product.category_name || 'N/A'}</span>
                         <span class="badge bg-info text-dark">${product.type_name || 'N/A'}</span>
                    </div>
                    <p class="fs-4 fw-bold mb-1">${stock} <span class="fs-6 fw-normal">units</span></p>
                    <p class="text-muted"><del>$${product.mrp || '0.00'}</del> $${product.sales_price}</p>
                    
                    <div class="mt-auto d-flex justify-content-end gap-2">
                         <button class="btn btn-sm btn-outline-danger btn-remove-stock" data-product-id="${product.id}" data-product-name="${product.name}" data-requires-serial="${product.requires_serial}">– Remove</button>
                         <button class="btn btn-sm btn-primary btn-add-stock" data-product-id="${product.id}" data-product-name="${product.name}" data-requires-serial="${product.requires_serial}">+ Add Stock</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}