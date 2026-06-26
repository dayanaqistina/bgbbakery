const cart = JSON.parse(localStorage.getItem('bgbCart') || '[]');

const productImages = [
    { pattern: /banana|muffin/i, src: '/images/bakes/banana-muffins.jpeg' },
    { pattern: /brownie/i, src: '/images/bakes/fudgy-brownies.jpeg' },
    { pattern: /orange|butter/i, src: '/images/bakes/orange-buttercake.jpeg' },
    { pattern: /chocolate|cupcake/i, src: '/images/bakes/chocolate-cupcakes.jpeg' },
    { pattern: /cheese/i, src: '/images/Untitled design (16).png' },
    { pattern: /carrot/i, src: '/images/Untitled design (12).png' }
];

function money(value) {
    return `RM${Number(value || 0).toFixed(2)}`;
}

function productImage(product) {
    const match = productImages.find((item) => item.pattern.test(product.name || ''));
    return match ? match.src : '/images/bakes/bake-box.jpeg';
}

function productCategory(product) {
    const name = product.name || '';
    if (/brownie/i.test(name)) {
        return 'brownies';
    }
    if (/cake|cheese|butter/i.test(name)) {
        return 'cakes';
    }
    return 'cupcakes';
}

function saveCart() {
    localStorage.setItem('bgbCart', JSON.stringify(cart));
}

function updateCartView() {
    document.querySelectorAll('[data-cart-count]').forEach((count) => {
        count.textContent = String(cart.length);
    });

    saveCart();

    const cartList = document.querySelector('[data-cart-list]');
    if (!cartList) {
        return;
    }

    if (cart.length === 0) {
        cartList.innerHTML = '<li>No items selected yet.</li>';
        return;
    }

    cartList.innerHTML = cart
        .map((item) => `<li>${item.name} <span>${money(item.price)}</span></li>`)
        .join('');
}

function bindProductButtons(scope = document) {
    scope.querySelectorAll('[data-product]').forEach((button) => {
        if (button.dataset.bound === 'true') {
            return;
        }

        button.dataset.bound = 'true';
        button.addEventListener('click', () => {
            cart.push({
                productId: button.dataset.productId ? Number(button.dataset.productId) : null,
                name: button.dataset.product,
                price: button.dataset.price ? Number(button.dataset.price) : 0,
                quantity: 1
            });
            updateCartView();
        });
    });
}

function bindFilters() {
    document.querySelectorAll('[data-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            document.querySelectorAll('[data-filter]').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            document.querySelectorAll('[data-category]').forEach((card) => {
                const shouldShow = filter === 'all' || card.dataset.category === filter;
                card.hidden = !shouldShow;
            });
        });
    });
}

async function loadProducts() {
    const grid = document.querySelector('[data-products-grid]');
    if (!grid) {
        return;
    }

    try {
        const response = await fetch('/api/products');
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        const products = await response.json();
        if (!Array.isArray(products) || products.length === 0) {
            bindProductButtons(grid);
            return;
        }

        grid.innerHTML = products.map((product) => {
            const category = productCategory(product);
            const image = productImage(product);
            const meta = product.stockQuantity == null ? 'Pre-order' : `${product.stockQuantity} available`;
            const description = product.flavourTopping || 'Freshly baked to order.';

            return `
                <article class="menu-card" data-category="${category}">
                    <span class="badge badge-dark">${meta}</span>
                    <img class="menu-image photo-image" src="${image}" alt="${product.name}" />
                    <div class="menu-copy">
                        <h3>${product.name}</h3>
                        <p>${description}</p>
                        <strong>${money(product.price)}</strong>
                        <button class="button button-dark" type="button"
                            data-product-id="${product.id}"
                            data-product="${product.name}"
                            data-price="${product.price}">Add to cart</button>
                    </div>
                </article>
            `;
        }).join('');

        bindProductButtons(grid);
    } catch (error) {
        bindProductButtons(grid);
    }
}

function orderPayload(form) {
    const data = new FormData(form);
    return {
        name: data.get('name'),
        phoneNumber: data.get('phone'),
        email: data.get('email'),
        fulfilment: data.get('fulfilment'),
        preferredDate: data.get('date') || null,
        notes: data.get('notes'),
        items: cart
            .filter((item) => item.productId)
            .map((item) => ({
                productId: item.productId,
                quantity: item.quantity || 1
            }))
    };
}

function bindOrderForm() {
    const form = document.querySelector('[data-order-form]');
    const message = document.querySelector('[data-order-message]');
    if (!form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (message) {
            message.textContent = '';
        }

        if (cart.length === 0) {
            if (message) {
                message.textContent = 'Add at least one product before submitting.';
            }
            return;
        }

        const payload = orderPayload(form);
        if (payload.items.length === 0) {
            if (message) {
                message.textContent = 'Reload the menu and add products from the database before submitting.';
            }
            return;
        }

        try {
            const response = await fetch('/api/shop/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || `HTTP ${response.status}`);
            }
            cart.length = 0;
            updateCartView();
            form.reset();
            if (message) {
                message.textContent = `Order ORD${String(result.orderId).padStart(5, '0')} submitted. Status: ${result.status}.`;
            }
        } catch (error) {
            if (message) {
                message.textContent = error.message || 'Order could not be submitted. Check the API and database connection.';
            }
        }
    });
}

const dataOutput = document.getElementById('dataOutput');
const productsBtn = document.getElementById('loadProductsBtn');
const customersBtn = document.getElementById('loadCustomersBtn');
const ordersBtn = document.getElementById('loadOrdersBtn');

function showResult(title, data) {
    if (!dataOutput) {
        return;
    }

    dataOutput.innerHTML = `
        <h3>${title}</h3>
        <pre>${JSON.stringify(data, null, 2)}</pre>
    `;
}

function handleError(error) {
    if (!dataOutput) {
        return;
    }

    dataOutput.innerHTML = `
        <h3>Error</h3>
        <pre>${error.message}</pre>
    `;
}

async function loadData(endpoint, title) {
    try {
        const response = await fetch(endpoint);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const data = await response.json();
        showResult(title, data);
    } catch (error) {
        handleError(error);
    }
}

if (productsBtn) {
    productsBtn.addEventListener('click', () => loadData('/api/products', 'Products'));
}

if (customersBtn) {
    customersBtn.addEventListener('click', () => loadData('/api/customers', 'Customers'));
}

if (ordersBtn) {
    ordersBtn.addEventListener('click', () => loadData('/api/orders', 'Orders'));
}

bindFilters();
bindProductButtons();
bindOrderForm();
loadProducts();
updateCartView();
