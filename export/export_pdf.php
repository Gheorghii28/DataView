<?php

$action = $_POST['action'];
$tableName = $_POST['tableName'];
$pdfUrl = 'tabelle_'. htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') .'.pdf';
$tableData = json_decode($_POST['tableData'], true);

$html = '<h1>'. htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') .'</h1>';
$html .= '<table border="1" cellpadding="5">';
$html .= '<thead><tr>';

foreach ($tableData['columns'] as $index => $column) {
    if (in_array($column['name'], ['id', 'created_at', 'user_id', 'display_order'])) {
        continue;
    }
    $html .= '<th>' . htmlspecialchars($column['name']) . '</th>';
}

$html .= '</tr></thead>';
$html .= '<tbody>';

foreach ($tableData['rows'] as $row) {
    $html .= '<tr>';
    foreach ($tableData['columns'] as $column) {
        if (in_array($column['name'], ['id', 'created_at', 'user_id', 'display_order'])) {
            continue;
        }
        $html .= '<td>' . htmlspecialchars($row[$column['name']]) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody></table>';

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('DataView');
$pdf->SetTitle($tableName);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTML($html, true, false, true, false, '');

$pdfContent = $pdf->Output($pdfUrl, $action);
$base64Pdf = base64_encode($pdfContent);

$response = [
    'status' => 'success',
    'pdf_base64' => $base64Pdf
];

header('Content-Type: application/json');
echo json_encode($response);