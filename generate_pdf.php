<?php
require_once('tcpdf/tcpdf.php');

class InvoicePDF extends TCPDF {
    public function Header() {
        // Get the current page width
        $pageWidth = $this->getPageWidth();
        
        // Skip logo processing since GD/Imagick is not available
        
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell($pageWidth - 30, 10, $_POST['invoiceTitle'], 0, 1, 'R');
        
        // Add a line separator
        $this->Line(15, 30, $pageWidth-15, 30);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create new PDF document
        $pdf = new InvoicePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Invoice Generator');
        $pdf->SetTitle($_POST['invoiceTitle']);

        // Set margins
        $pdf->SetMargins(15, 40, 15);
        $pdf->SetHeaderMargin(20);
        $pdf->SetFooterMargin(15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // From Address Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'From:', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 6, 
            $_POST['fromName'] . "\n" .
            $_POST['fromEmail'] . "\n" .
            $_POST['fromAddress'] . "\n" .
            $_POST['fromCity'] . ", " . $_POST['fromState'] . " " . $_POST['fromZip'] . "\n" .
            "Phone: " . $_POST['fromPhone'],
            0, 'L', 0, 1, '', '', true);

        // Bill To Section
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Bill To:', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 6, 
            $_POST['toName'] . "\n" .
            $_POST['toEmail'] . "\n" .
            $_POST['toAddress'] . "\n" .
            $_POST['toCity'] . ", " . $_POST['toState'] . " " . $_POST['toZip'] . "\n" .
            "Phone: " . $_POST['toPhone'],
            0, 'L', 0, 1, '', '', true);

        // Invoice Details
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(95, 10, 'Invoice Number: ' . $_POST['invoiceNumber'], 0, 0);
        $pdf->Cell(95, 10, 'Date: ' . $_POST['invoiceDate'], 0, 1);
        
        // Terms
        $terms = $_POST['terms'] === 'on_receipt' ? 'Due on Receipt' : 'Net ' . $_POST['paymentDays'] . ' Days';
        $pdf->Cell(95, 10, 'Terms: ' . $terms, 0, 1);

        // Items Table Header
        $pdf->Ln(10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(80, 10, 'Description', 1, 0, 'C', true);
        $pdf->Cell(35, 10, 'Rate', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'QTY', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'Amount', 1, 1, 'C', true);

        // Items Table Content
        $pdf->SetFont('helvetica', '', 12);
        $total = 0;

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $amount = $item['rate'] * $item['qty'];
                $pdf->Cell(80, 10, $item['description'], 1, 0, 'L');
                $pdf->Cell(35, 10, '$' . number_format($item['rate'], 2), 1, 0, 'R');
                $pdf->Cell(25, 10, $item['qty'], 1, 0, 'C');
                $pdf->Cell(50, 10, '$' . number_format($amount, 2), 1, 1, 'R');
                $total += $amount;
            }
        }

        // Discount
        $discount = 0;
        if (isset($_POST['discountType']) && $_POST['discountType'] !== 'none' && !empty($_POST['discountValue'])) {
            if ($_POST['discountType'] === 'percentage') {
                $discount = $total * ($_POST['discountValue'] / 100);
            } else {
                $discount = floatval($_POST['discountValue']);
            }
        }

        // Subtotal
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(140, 10, 'Subtotal:', 1, 0, 'R');
        $pdf->Cell(50, 10, '$' . number_format($total, 2), 1, 1, 'R');

        // Discount if applicable
        if ($discount > 0) {
            $pdf->Cell(140, 10, 'Discount:', 1, 0, 'R');
            $pdf->Cell(50, 10, '-$' . number_format($discount, 2), 1, 1, 'R');
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(140, 10, 'Total:', 1, 0, 'R');
        $pdf->Cell(50, 10, '$' . number_format($total - $discount, 2), 1, 1, 'R');

        // Notes Section
        if (!empty($_POST['notes'])) {
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Notes:', 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->MultiCell(0, 6, $_POST['notes'], 0, 'L');
        }

        // Output the PDF
        $pdf->Output('invoice.pdf', 'D');
    } catch (Exception $e) {
        echo 'Error generating PDF: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request method';
}
?>
