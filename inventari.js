
const initialProducts = [
  {
    id: 101,
    name: "Nike Air Max",
    category: "Këpucë",
    quantity: 20,
    price: 89.99,
    photo: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=100"
  },
  {
    id: 102,
    name: "T-shirt e bardhë",
    category: "Veshje",
    quantity: 35,
    price: 19.99,
    photo: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=100"
  },
  {
    id: 103,
    name: "Atlete sportive",
    category: "Këpucë",
    quantity: 8,
    price: 59.99,
    photo: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=100"
  },
  {
    id: 104,
    name: "Çantë e zezë",
    category: "Aksesorë",
    quantity: 0,
    price: 39.99,
    photo: "https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=100"
  },
  {
    id: 105,
    name: "Orë dore",
    category: "Aksesorë",
    quantity: 12,
    price: 129.99,
    photo: "https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=100"
  },
  {
    id: 106,
    name: "Syze dielli",
    category: "Aksesorë",
    quantity: 2,
    price: 24.99,
    photo: "https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=100"
  }
];

let products = initialProducts.map(p => ({ ...p }));

const $ = id => document.getElementById(id);

const euro = n =>
  Number(n).toLocaleString("de-DE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }) + " €";

function escapeHTML(value) {
  return String(value).replace(/[&<>"']/g, c => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  })[c]);
}

function status(product) {
  if (product.quantity === 0) {
    return '<span class="badge out">Pa stok</span>';
  }

  if (product.quantity <= 5) {
    return '<span class="badge low">Stok i ulët</span>';
  }

  return '<span class="badge available">Në stok</span>';
}

function render() {
  const query = $("search").value.toLowerCase().trim();
  const category = $("filter").value;

  const filtered = products.filter(product =>
    (
      product.name.toLowerCase().includes(query) ||
      String(product.id).includes(query)
    ) &&
    (!category || product.category === category)
  );

  $("products").innerHTML = filtered.length
    ? filtered.map(product => `
      <tr>
        <td>#${product.id}</td>
        <td>
          <div class="product">
            <img
              src="${escapeHTML(product.photo || "")}"
              alt=""
              onerror="this.style.visibility='hidden'"
            >
            <div>
              <div class="product-name">
                ${escapeHTML(product.name)}
              </div>
              <div class="product-id">
                SKU-${product.id}
              </div>
            </div>
          </div>
        </td>
        <td>${escapeHTML(product.category)}</td>
        <td>${product.quantity} copë</td>
        <td><b>${euro(product.price)}</b></td>
        <td>${status(product)}</td>
        <td>
          <div class="actions">
            <button
              class="edit"
              onclick="editProduct(${product.id})"
              title="Edito"
            >✎</button>

            <button
              class="delete"
              onclick="deleteProduct(${product.id})"
              title="Fshije"
            >✕</button>
          </div>
        </td>
      </tr>
    `).join("")
    : `
      <tr>
        <td colspan="7" class="empty">
          Nuk u gjet asnjë produkt.
        </td>
      </tr>
    `;

  $("total").textContent = products.length;

  $("stock").textContent =
    products.filter(p => p.quantity > 5).length;

  $("low").textContent =
    products.filter(p => p.quantity <= 5).length;

  const inventoryValue = products.reduce(
    (sum, product) => sum + product.price * product.quantity,
    0
  );

  $("value").textContent = euro(inventoryValue);

  $("count").textContent =
    `Duke shfaqur ${filtered.length} nga ${products.length} produkte`;
}

function openModal(product = null) {
  $("productForm").reset();
  $("editId").value = product ? product.id : "";

  $("modalTitle").textContent = product
    ? "Ndrysho produktin"
    : "Shto produkt të ri";

  if (product) {
    $("name").value = product.name;
    $("category").value = product.category;
    $("price").value = product.price;
    $("quantity").value = product.quantity;
    $("photo").value = product.photo || "";
  }

  $("modalBg").style.display = "flex";
}

function closeModal() {
  $("modalBg").style.display = "none";
}

function editProduct(id) {
  const product = products.find(p => p.id === id);

  if (product) {
    openModal(product);
  }
}

function deleteProduct(id) {
  const product = products.find(p => p.id === id);

  if (
    product &&
    confirm(`A dëshiron ta fshish "${product.name}"?`)
  ) {
    products = products.filter(p => p.id !== id);
    render();
  }
}

$("productForm").addEventListener("submit", event => {
  event.preventDefault();

  const editId = $("editId").value;

  const product = {
    id: editId
      ? Number(editId)
      : Math.max(100, ...products.map(p => p.id)) + 1,

    name: $("name").value.trim(),
    category: $("category").value,
    price: Number($("price").value),
    quantity: Number($("quantity").value),
    photo: $("photo").value.trim()
  };

  if (editId) {
    products = products.map(p =>
      p.id === Number(editId) ? product : p
    );
  } else {
    products.unshift(product);
  }

  closeModal();
  render();
});

$("search").addEventListener("input", render);
$("filter").addEventListener("change", render);

$("globalSearch").addEventListener("input", () => {
  $("search").value = $("globalSearch").value;
  render();
});

$("modalBg").addEventListener("click", event => {
  if (event.target === $("modalBg")) {
    closeModal();
  }
});

render();
