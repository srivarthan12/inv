// =================================================================
//  1. HELPER FUNCTIONS (Defined in the global scope)
// =================================================================

async function handleFormSubmit(data, action, handlerUrl) {
    const formData = (data instanceof HTMLFormElement) ? new FormData(data) : data;
    if (!formData.has('action')) formData.append('action', action);

    try {
        const response = await fetch(handlerUrl, { method: 'POST', body: formData });
        if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);
        const result = await response.json();

        if (result.status === 'success') {
            alert(result.message);
            if (data instanceof HTMLFormElement) {
                const modal = data.closest('.modal');
                if (modal) bootstrap.Modal.getInstance(modal)?.hide();
                data.reset();
            }
            location.reload();
        } else {
            alert(`Error: ${result.message || 'An unknown error occurred.'}`);
        }
    } catch (error) {
        console.error('Submission failed:', error);
        alert('A critical network or server error occurred. Check the console.');
    }
}

function populateDropdown(selectElement, data, defaultOptionText) {
    selectElement.innerHTML = `<option value="">-- ${defaultOptionText} --</option>`;
    data.forEach(item => { const option = new Option(item.name, item.id); selectElement.add(option); });
    selectElement.disabled = false;
}

function createProductCard(product) {
    let stock = parseInt(product.stock_available || 0), minStock = parseInt(product.min_stock || 0);
    let statusClass = 'tag-stock-sufficient', statusText = 'Sufficient';
    if (stock <= 0) { statusClass = 'tag-stock-zero'; statusText = 'Out of Stock'; } 
    else if (stock <= minStock) { statusClass = 'tag-stock-low'; statusText = 'Low Stock'; }
    return `<div class="col-sm-6 col-lg-4 mb-4 product-card-wrapper"><div class="card h-100"><div class="card-body d-flex flex-column"><h5 class="card-title">${product.name}</h5><p class="card-subtitle mb-2"><span class="badge ${statusClass}">${statusText}</span></p><div class="mb-2"><span class="badge bg-secondary">${product.category_name || 'N/A'}</span> <span class="badge bg-info text-dark">${product.type_name || 'N/A'}</span></div><p class="fs-4 fw-bold mb-1">${stock} <span class="fs-6 fw-normal">units</span></p><p class="text-muted"><del>$${product.mrp || '0.00'}</del> $${product.sales_price}</p><div class="mt-auto d-flex justify-content-end gap-2"><button class="btn btn-sm btn-outline-danger btn-remove-stock" data-product-id="${product.id}" data-product-name="${product.name}" data-requires-serial="${product.requires_serial}">– Remove</button><button class="btn btn-sm btn-primary btn-add-stock" data-product-id="${product.id}" data-product-name="${product.name}" data-requires-serial="${product.requires_serial}">+ Add Stock</button></div></div></div></div>`;
}

// =================================================================
//  2. MAIN APPLICATION LOGIC (Waits for the page to be ready)
// =================================================================
document.addEventListener('DOMContentLoaded', function() {

    // --- Global Listeners ---
    document.body.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.btn-delete-item');
        if (deleteButton) { e.preventDefault(); const { id, table, name } = deleteButton.dataset; if (confirm(`Delete "${name || 'this item'}"?`)) { const fd = new FormData(); fd.append('id', id); fd.append('table', table); handleFormSubmit(fd, 'delete', 'ajax/product_handler.php'); } }
        const editButton = e.target.closest('.btn-edit-item');
        if (editButton) { e.preventDefault(); alert(`Edit for product ID ${editButton.dataset.id} is a future feature.`); }
    });
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.dataset.action && form.dataset.handler) { e.preventDefault(); handleFormSubmit(form, form.dataset.action, form.dataset.handler); }
    });

    // --- PRODUCT MANAGEMENT PAGE LOGIC ---
    if (document.getElementById('product-list-container')) {
        async function loadProducts() {
            const container = document.getElementById('product-list-container');
            const response = await fetch('ajax/get_data.php?action=get_products');
            const products = await response.json();
            container.innerHTML = ''; 
            if (products.length === 0) { container.innerHTML = '<tr><td colspan="5" class="text-center">No products found.</td></tr>'; return; }
            products.forEach(p => { container.innerHTML += `<tr><td>${p.name}</td><td><span class="badge bg-secondary">${p.category_name||'N/A'}</span><span class="badge bg-info text-dark">${p.type_name||'N/A'}</span><span class="badge bg-light text-dark">${p.variant_name||'N/A'}</span></td><td>$${p.sales_price||'0.00'}/<del>$${p.mrp||'0.00'}</del></td><td>${p.stock_available||0}</td><td><button class="btn btn-sm btn-outline-primary btn-edit-item" data-id="${p.id}"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger btn-delete-item" data-id="${p.id}" data-table="products" data-name="${p.name}"><i class="bi bi-trash"></i></button></td></tr>`; });
        }
        loadProducts();
        const pCatSelect = document.getElementById('product_category'), pTypeSelect = document.getElementById('product_type'), pVarSelect = document.getElementById('product_variant');
        pCatSelect?.addEventListener('change', async function() { pTypeSelect.disabled = true; pVarSelect.disabled = true; if (this.value) { const r = await fetch(`ajax/get_data.php?action=get_types&category_id=${this.value}`); populateDropdown(pTypeSelect, await r.json(), 'Select Type'); } });
        pTypeSelect?.addEventListener('change', async function() { pVarSelect.disabled = true; if (this.value) { const r = await fetch(`ajax/get_data.php?action=get_variants&type_id=${this.value}`); populateDropdown(pVarSelect, await r.json(), 'Select Variant'); } });
        const vCatSelect = document.getElementById('variant_category_select'), vTypeSelect = document.getElementById('variant_type_id');
        vCatSelect?.addEventListener('change', async function() { vTypeSelect.disabled = true; if (this.value) { const r = await fetch(`ajax/get_data.php?action=get_types&category_id=${this.value}`); populateDropdown(vTypeSelect, await r.json(), 'Select Type'); } });
    }

    // --- STOCK MANAGEMENT PAGE LOGIC ---
    if (document.getElementById('product-grid')) {
        const stockGrid = document.getElementById('product-grid');
        let allProducts = [];
        async function loadStockDashboard() { try { const response = await fetch('ajax/get_data.php?action=get_products'); allProducts = await response.json(); renderProductCards(allProducts); } catch (error) { console.error('Failed to load stock data:', error); } }
        function renderProductCards(productsToRender) {
            stockGrid.innerHTML = ''; if (productsToRender.length === 0) { stockGrid.innerHTML = '<div class="col-12"><p class="text-center text-muted">No products found.</p></div>'; return; }
            let low=0, zero=0; allProducts.forEach(p => { const s = parseInt(p.stock_available||0), m = parseInt(p.min_stock||0); if (s===0) zero++; else if (s>0 && s<=m) low++; });
            document.getElementById('low-stock-count').textContent=low; document.getElementById('zero-stock-count').textContent=zero;
            productsToRender.forEach(p => stockGrid.insertAdjacentHTML('beforeend', createProductCard(p)));
        }
        stockGrid.addEventListener('click', async e => {
            const addBtn = e.target.closest('.btn-add-stock'), removeBtn = e.target.closest('.btn-remove-stock');
            if (addBtn) { const { productId, productName, requiresSerial } = addBtn.dataset; document.getElementById('addStockProductId').value = productId; document.getElementById('addStockProductName').textContent = productName; const sCont = document.getElementById('add-serial-number-inputs-container'), sInp = document.getElementById('add-serial-number-inputs'), qty = document.getElementById('add_quantity'); qty.value = '1'; sInp.innerHTML = ''; if (requiresSerial==='1') { sCont.classList.remove('d-none'); qty.oninput = () => { sInp.innerHTML = Array.from({length:qty.value}, (_, i) => `<input type="text" name="serial_numbers[]" class="form-control mb-2" placeholder="Serial #${i + 1}" required>`).join(''); }; qty.dispatchEvent(new Event('input')); } else { sCont.classList.add('d-none'); qty.oninput = null; } new bootstrap.Modal(document.getElementById('addStockModal')).show(); }
            if (removeBtn) { const { productId, productName, requiresSerial } = removeBtn.dataset; document.getElementById('removeStockProductId').value = productId; document.getElementById('removeStockProductName').textContent = productName; const sCont = document.getElementById('remove-serial-number-inputs-container'), sInp = document.getElementById('remove-serial-number-inputs'), qty = document.getElementById('remove_quantity'); qty.value = '0'; qty.readOnly = true; sInp.innerHTML = '<div class="spinner-border spinner-border-sm"></div>'; if (requiresSerial==='1') { sCont.classList.remove('d-none'); const res = await fetch(`ajax/get_data.php?action=get_serials&product_id=${productId}`); const serials = await res.json(); sInp.innerHTML = ''; if (serials.length > 0) { serials.forEach(s => { sInp.innerHTML += `<div class="form-check"><input class="form-check-input" type="checkbox" name="serial_ids[]" value="${s.id}" id="serial_${s.id}"><label class="form-check-label" for="serial_${s.id}">${s.serial_no}</label></div>`; }); sInp.onchange = () => qty.value = sInp.querySelectorAll(':checked').length; } else { sInp.innerHTML = '<p class="text-danger">No serials available.</p>'; } } else { sCont.classList.add('d-none'); qty.readOnly = false; qty.value = '1'; sInp.onchange = null; } new bootstrap.Modal(document.getElementById('removeStockModal')).show(); }
        });
        loadStockDashboard();
    }

    // --- EXPO STOCK PAGE LOGIC ---
    if (document.getElementById('expo-stock-list')) {
        let currentBill = [];
        async function loadExpoStock() {
            const container = document.getElementById('expo-stock-list');
            try {
                const response = await fetch('ajax/get_data.php?action=get_expo_stock_details');
                const expoStock = await response.json();
                container.innerHTML = '';
                if (expoStock.length === 0) { container.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No stock at expo.</td></tr>'; return; }
                expoStock.forEach(item => { container.innerHTML += `<tr><td>${item.name}</td><td>${item.quantity}</td><td class="no-print"><button class="btn btn-sm btn-info btn-bill-item" data-product-id="${item.product_id}" data-product-name="${item.name}" data-product-price="${item.sales_price}" data-requires-serial="${item.requires_serial}">Bill</button> <button class="btn btn-sm btn-warning btn-return-item" data-product-id="${item.product_id}" data-product-name="${item.name}" data-requires-serial="${item.requires_serial}">Return</button></td></tr>`; });
            } catch (error) { console.error("Failed to load expo stock", error); }
        }
        loadExpoStock();
        
        document.body.addEventListener('click', async e => {
            if (e.target.id === 'printExpoSummaryBtn') window.print();
            if (e.target.id === 'returnAllFromExpoBtn') if(confirm("Return all stock?")) handleFormSubmit(new FormData(), 'returnAllFromExpo', 'ajax/expo_handler.php');
            
            const returnBtn = e.target.closest('.btn-return-item');
            if (returnBtn) { const { productId, productName, requiresSerial } = returnBtn.dataset; document.getElementById('returnItemForm').reset(); document.getElementById('returnItemName').textContent = productName; document.getElementById('returnProductId').value = productId; const sCont = document.getElementById('return-serial-select-container'), sInp = document.getElementById('return-serial-select-inputs'), qty = document.getElementById('returnQuantity'); sCont.classList.add('d-none'); sInp.innerHTML = ''; qty.readOnly = false; if (requiresSerial === '1') { qty.readOnly = true; qty.value = 0; sCont.classList.remove('d-none'); sInp.innerHTML = '<div class="spinner-border spinner-border-sm"></div>'; const res = await fetch(`ajax/get_data.php?action=get_expo_serials&product_id=${productId}`); const serials = await res.json(); sInp.innerHTML = ''; if (serials.length > 0) { serials.forEach(s => { sInp.innerHTML += `<div class="form-check"><input class="form-check-input" type="checkbox" name="serial_ids[]" value="${s.id}" id="return_serial_${s.id}"><label for="return_serial_${s.id}">${s.serial_no}</label></div>`; }); sInp.onchange = () => qty.value = sInp.querySelectorAll(':checked').length; } else { sInp.innerHTML = '<p class="text-danger">No serials to return.</p>'; } } else { qty.value = 1; } new bootstrap.Modal(document.getElementById('returnItemModal')).show(); }
            
            const billBtn = e.target.closest('.btn-bill-item');
            if (billBtn) { const { productId, productName, productPrice, requiresSerial } = billBtn.dataset; document.getElementById('addItemToBillForm').reset(); document.getElementById('bill_product_id').value = productId; document.getElementById('bill_product_name').textContent = productName; document.getElementById('bill_product_price').value = productPrice; document.getElementById('bill_product_requires_serial').value = requiresSerial; const sCont = document.getElementById('bill-serial-select-container'), sInp = document.getElementById('bill-serial-select-inputs'), qty = document.getElementById('bill_quantity'); sCont.classList.add('d-none'); sInp.innerHTML = ''; qty.readOnly = false; if (requiresSerial === '1') { qty.readOnly = true; qty.value = 0; sCont.classList.remove('d-none'); sInp.innerHTML = '<div class="spinner-border spinner-border-sm"></div>'; const res = await fetch(`ajax/get_data.php?action=get_expo_serials&product_id=${productId}`); const serials = await res.json(); sInp.innerHTML = ''; if (serials.length > 0) { serials.forEach(s => { sInp.innerHTML += `<div class="form-check"><input class="form-check-input" type="checkbox" value="${s.serial_no}" id="bill_serial_${s.id}"><label for="bill_serial_${s.id}">${s.serial_no}</label></div>`; }); sInp.onchange = () => qty.value = sInp.querySelectorAll(':checked').length; } else { sInp.innerHTML = '<p class="text-danger">No available serials.</p>'; } } else { qty.value = 1; } new bootstrap.Modal(document.getElementById('expoBillingModal')).show(); }
        });

        document.getElementById('expo_product_select')?.addEventListener('change', async function() { const selectedOption = this.options[this.selectedIndex], requiresSerial = selectedOption.dataset.requiresSerial === '1', productId = this.value; const sCont = document.getElementById('expo-serial-select-container'), sInp = document.getElementById('expo-serial-select-inputs'), qty = document.querySelector('#moveToExpoForm input[name="quantity"]'); qty.value = ''; qty.readOnly = false; sInp.innerHTML = '<div class="spinner-border spinner-border-sm"></div>'; if (requiresSerial && productId) { qty.readOnly = true; sCont.classList.remove('d-none'); const res = await fetch(`ajax/get_data.php?action=get_serials&product_id=${productId}`); const serials = await res.json(); sInp.innerHTML = ''; if(serials.length > 0) { serials.forEach(s => { sInp.innerHTML += `<div class="form-check"><input class="form-check-input" type="checkbox" name="serial_ids[]" value="${s.id}" id="expo_serial_${s.id}"><label for="expo_serial_${s.id}">${s.serial_no}</label></div>`; }); sInp.onchange = () => qty.value = sInp.querySelectorAll(':checked').length; } else { sInp.innerHTML = '<p class="text-danger">No serials available.</p>'; } } else { sCont.classList.add('d-none'); } });

        document.getElementById('addItemToBillForm').addEventListener('submit', function(e) { e.preventDefault(); const newItem = { product_id: document.getElementById('bill_product_id').value, name: document.getElementById('bill_product_name').textContent, price: parseFloat(document.getElementById('bill_product_price').value), quantity: parseInt(document.getElementById('bill_quantity').value), serials: [] }; if (document.getElementById('bill_product_requires_serial').value === '1') { const selSerials = document.querySelectorAll('#bill-serial-select-inputs :checked'); selSerials.forEach(s => newItem.serials.push(s.value)); if (selSerials.length !== newItem.quantity) { alert('Qty must match selected serials.'); return; } } if (!newItem.quantity || newItem.quantity <= 0) { alert('Qty must be > 0.'); return; } currentBill.push(newItem); renderCurrentBill(); e.target.reset(); const qty = document.getElementById('bill_quantity'); if (qty.readOnly) qty.value = 0; else qty.value = 1; });
        function renderCurrentBill() { const container = document.getElementById('current-bill-items'); container.innerHTML = ''; if (currentBill.length === 0) { container.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added.</td></tr>'; return; } currentBill.forEach((item, index) => { container.innerHTML += `<tr><td>${item.name}</td><td>${item.quantity}</td><td>$${item.price.toFixed(2)}</td><td>$${(item.quantity * item.price).toFixed(2)}</td><td><button class="btn btn-sm btn-danger btn-remove-bill-item" data-index="${index}">X</button></td></tr>`; }); }
        document.getElementById('current-bill-items').addEventListener('click', e => { if(e.target.classList.contains('btn-remove-bill-item')) { currentBill.splice(e.target.dataset.index, 1); renderCurrentBill(); } });
        document.getElementById('saveBillBtn').addEventListener('click', () => { if (currentBill.length === 0) { alert("Cannot save an empty bill."); return; } const fd = new FormData(); fd.append('items_json', JSON.stringify(currentBill)); handleFormSubmit(fd, 'createExpoBill', 'ajax/expo_handler.php'); currentBill = []; });
    }

}); // THIS IS THE FINAL CLOSING BRACKET FOR DOMCONTENTLOADED