<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Create Invoice</h1>
        <form method="POST" action="generate_pdf.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="invoiceTitle">Invoice Title:</label>
                <input type="text" id="invoiceTitle" name="invoiceTitle" value="Invoice" required>
            </div>

            <div class="form-group">
                <label for="logo">Insert Logo (max size 2MB):</label>
                <input type="file" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
                <img id="logoPreview" class="logo-preview" style="display: none;">
            </div>

            <div class="address-section">
                <h2>From</h2>
                <div class="form-group">
                    <label for="fromName">Name:</label>
                    <input type="text" id="fromName" name="fromName" required>
                </div>
                <div class="form-group">
                    <label for="fromEmail">Email Address:</label>
                    <input type="email" id="fromEmail" name="fromEmail" required>
                </div>
                <div class="form-group">
                    <label for="fromAddress">Street:</label>
                    <input type="text" id="fromAddress" name="fromAddress" required>
                </div>
                <div class="form-group">
                    <label for="fromCity">City:</label>
                    <input type="text" id="fromCity" name="fromCity" required>
                </div>
                <div class="form-group">
                    <label for="fromState">State:</label>
                    <input type="text" id="fromState" name="fromState" required>
                </div>
                <div class="form-group">
                    <label for="fromZip">Zip:</label>
                    <input type="text" id="fromZip" name="fromZip" required>
                </div>
                <div class="form-group">
                    <label for="fromPhone">Phone:</label>
                    <input type="text" id="fromPhone" name="fromPhone" required>
                </div>
            </div>

            <div class="address-section">
                <h2>Bill To</h2>
                <div class="form-group">
                    <label for="toName">Name:</label>
                    <input type="text" id="toName" name="toName" required>
                </div>
                <div class="form-group">
                    <label for="toEmail">Email Address:</label>
                    <input type="email" id="toEmail" name="toEmail" required>
                </div>
                <div class="form-group">
                    <label for="toAddress">Street:</label>
                    <input type="text" id="toAddress" name="toAddress" required>
                </div>
                <div class="form-group">
                    <label for="toCity">City:</label>
                    <input type="text" id="toCity" name="toCity" required>
                </div>
                <div class="form-group">
                    <label for="toState">State:</label>
                    <input type="text" id="toState" name="toState" required>
                </div>
                <div class="form-group">
                    <label for="toZip">Zip:</label>
                    <input type="text" id="toZip" name="toZip" required>
                </div>
                <div class="form-group">
                    <label for="toPhone">Phone:</label>
                    <input type="text" id="toPhone" name="toPhone" required>
                </div>
            </div>

            <div class="form-group">
                <h2>Invoice Details</h2>
                <div class="form-group">
                    <label for="invoiceNumber">Invoice Number:</label>
                    <input type="text" id="invoiceNumber" name="invoiceNumber" required>
                </div>
                <div class="form-group">
                    <label for="invoiceDate">Date:</label>
                    <input type="date" id="invoiceDate" name="invoiceDate" required>
                </div>
                <div class="form-group">
                    <label for="terms">Terms:</label>
                    <select id="terms" name="terms" onchange="handleTermsChange()">
                        <option value="on_receipt">On Receipt</option>
                        <option value="specific_days">Specific Days</option>
                    </select>
                    <div id="specificDaysInput" style="display: none;">
                        <label for="paymentDays">Number of Days:</label>
                        <input type="number" id="paymentDays" name="paymentDays" min="1">
                    </div>
                </div>
            </div>

            <div class="items-section">
                <h2>Items</h2>
                <div id="itemsContainer">
                    <div class="item">
                        <input type="text" name="items[0][description]" placeholder="Item Description" required>
                        <input type="number" name="items[0][rate]" placeholder="Rate/Cost" step="0.01" onchange="calculateAmount(0)" required>
                        <input type="number" name="items[0][qty]" placeholder="Quantity" onchange="calculateAmount(0)" required>
                        <input type="number" name="items[0][amount]" placeholder="Amount" readonly>
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" class="btn-add" onclick="addItem()">Add Item</button>
                    <button type="button" class="btn-remove" onclick="removeItem()">Remove Item</button>
                </div>
            </div>

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <label for="discount">Discount:</label>
                    <select id="discountType" name="discountType" onchange="calculateTotal()">
                        <option value="none">None</option>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                    <input type="number" id="discountValue" name="discountValue" step="0.01" onchange="calculateTotal()" style="display: none;">
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span id="grandTotal">$0.00</span>
                </div>
            </div>

            <div class="notes-section">
                <h2>Notes</h2>
                <textarea name="notes" rows="4" placeholder="Add any additional notes or terms"></textarea>
            </div>

            <button type="submit" class="btn-generate">Generate Invoice</button>
        </form>
    </div>

    <script>
        // Logo preview
        function previewLogo(event) {
            const preview = document.getElementById('logoPreview');
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Terms handling
        function handleTermsChange() {
            const termsSelect = document.getElementById('terms');
            const specificDaysInput = document.getElementById('specificDaysInput');
            specificDaysInput.style.display = termsSelect.value === 'specific_days' ? 'block' : 'none';
        }

        // Items handling
        let itemCount = 1;

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'item';
            newItem.innerHTML = `
                <input type="text" name="items[${itemCount}][description]" placeholder="Item Description" required>
                <input type="number" name="items[${itemCount}][rate]" placeholder="Rate/Cost" step="0.01" onchange="calculateAmount(${itemCount})" required>
                <input type="number" name="items[${itemCount}][qty]" placeholder="Quantity" onchange="calculateAmount(${itemCount})" required>
                <input type="number" name="items[${itemCount}][amount]" placeholder="Amount" readonly>
            `;
            container.appendChild(newItem);
            itemCount++;
        }

        function removeItem() {
            const container = document.getElementById('itemsContainer');
            if (container.children.length > 1) {
                container.removeChild(container.lastChild);
                itemCount--;
                calculateTotal();
            }
        }

        function calculateAmount(index) {
            const rate = document.querySelector(`input[name="items[${index}][rate]"]`).value;
            const qty = document.querySelector(`input[name="items[${index}][qty]"]`).value;
            const amount = rate * qty;
            document.querySelector(`input[name="items[${index}][amount]"]`).value = amount.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let subtotal = 0;
            const items = document.getElementsByClassName('item');
            
            Array.from(items).forEach(item => {
                const amount = parseFloat(item.querySelector('input[name$="[amount]"]').value) || 0;
                subtotal += amount;
            });

            const discountType = document.getElementById('discountType').value;
            const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
            let discount = 0;

            if (discountType === 'percentage') {
                discount = subtotal * (discountValue / 100);
            } else if (discountType === 'fixed') {
                discount = discountValue;
            }

            const total = subtotal - discount;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('grandTotal').textContent = `$${total.toFixed(2)}`;
        }

        // Handle discount type change
        document.getElementById('discountType').addEventListener('change', function() {
            const discountValue = document.getElementById('discountValue');
            discountValue.style.display = this.value === 'none' ? 'none' : 'inline-block';
            if (this.value === 'percentage') {
                discountValue.placeholder = 'Enter percentage';
                discountValue.max = '100';
            } else if (this.value === 'fixed') {
                discountValue.placeholder = 'Enter amount';
                discountValue.removeAttribute('max');
            }
        });
    </script>
</body>
</html>
