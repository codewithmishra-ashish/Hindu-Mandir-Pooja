let totalAmount = 0;

function toggleToPlusMinus(button, amount) {
    // Hide the "Add" button and show the Plus-Minus container
    button.style.display = "none";
    const plusMinusContainer = button.nextElementSibling; // Locate the plus-minus-container

    if (plusMinusContainer && plusMinusContainer.classList.contains("plus-minus-container")) {
        plusMinusContainer.style.display = "inline-flex"; // Show the plus-minus container
        totalAmount += amount; // Add the initial amount
        document.getElementById("totalAmount").textContent = totalAmount;
    }
}

function adjustTotal(button, amount) {
    const container = button.parentElement;
    const quantitySpan = container.querySelector(".quantity");
    let quantity = parseInt(quantitySpan.textContent);

    // Update the quantity
    quantity += amount > 0 ? 1 : -1;

    if (quantity > 0) {
        quantitySpan.textContent = quantity; // Update quantity
        totalAmount += amount; // Adjust total amount
        document.getElementById("totalAmount").textContent = totalAmount;
    } else {
        // Hide Plus-Minus and show Add button if quantity is zero
        container.style.display = "none";
        container.previousElementSibling.style.display = "inline-block"; // Show Add button
        totalAmount -= amount; // Reset total amount for removed item
        document.getElementById("totalAmount").textContent = totalAmount;
    }
}
