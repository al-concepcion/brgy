<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if (!$id) {
    die('Invalid certificate request');
}

// Get certificate details - verify it belongs to the logged-in user
try {
    $stmt = $conn->prepare("SELECT * FROM certification_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificate) {
        die('Certificate not found or access denied');
    }
    
    // Only allow viewing if status is Ready or Completed
    if (!in_array($certificate['status'], ['Ready for Pickup', 'Ready for Delivery', 'Completed'])) {
        die('Certificate is not yet ready for printing');
    }
} catch(PDOException $e) {
    die('Error retrieving certificate');
}

$cert_type = $certificate['certificate_type'];
$full_name = $certificate['first_name'] . ' ' . 
             ($certificate['middle_name'] ? $certificate['middle_name'] . ' ' : '') . 
             $certificate['last_name'];

// Certificate titles
$cert_titles = [
    'residency' => 'CERTIFICATE OF RESIDENCY',
    'indigency' => 'CERTIFICATE OF INDIGENCY',
    'clearance' => 'BARANGAY CLEARANCE',
    'business' => 'BARANGAY BUSINESS CLEARANCE',
    'good_moral' => 'CERTIFICATE OF GOOD MORAL CHARACTER'
];

$cert_title = $cert_titles[$cert_type] ?? 'BARANGAY CERTIFICATE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate - <?php echo $certificate['reference_number']; ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body {
                padding: 0;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .certificate-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .certificate {
            padding: 30mm 25mm;
            position: relative;
        }
        
        .certificate-border {
            position: absolute;
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 3px solid #0b5d3c;
            border-radius: 10px;
        }
        
        .inner-border {
            position: absolute;
            top: 18mm;
            left: 18mm;
            right: 18mm;
            bottom: 18mm;
            border: 1px solid #ffd700;
        }
        
        .header {
            text-align: center;
            position: relative;
            z-index: 1;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }
        
        .republic {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .province {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .municipality {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .barangay {
            font-size: 20px;
            font-weight: bold;
            color: #0b5d3c;
            margin-bottom: 5px;
        }
        
        .office {
            font-size: 12px;
            font-style: italic;
            color: #666;
        }
        
        .divider {
            width: 200px;
            height: 2px;
            background: #0b5d3c;
            margin: 15px auto;
        }
        
        .cert-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #0b5d3c;
            margin: 30px 0;
            text-decoration: underline;
        }
        
        .cert-body {
            text-align: justify;
            font-size: 14px;
            line-height: 2;
            text-indent: 50px;
            margin: 30px 0;
            position: relative;
            z-index: 1;
        }
        
        .recipient-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 16px;
        }
        
        .purpose {
            font-style: italic;
            font-weight: bold;
        }
        
        .signatures {
            margin-top: 50px;
            position: relative;
            z-index: 1;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-block {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
            height: 50px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .signature-title {
            font-size: 12px;
            font-style: italic;
        }
        
        .doc-stamp {
            text-align: right;
            margin-top: 30px;
            font-size: 12px;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            opacity: 0.03;
            color: #0b5d3c;
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
        
        .print-button {
            margin: 20px auto;
            display: block;
            padding: 12px 30px;
            background: #0b5d3c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        
        .print-button:hover {
            background: #12754a;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print Certificate</button>
    
    <div class="certificate-wrapper">
        <div class="certificate-border"></div>
        <div class="inner-border"></div>
        <div class="watermark">OFFICIAL</div>
        
        <div class="certificate">
            <div class="header">
                <div class="republic">Republic of the Philippines</div>
                <div class="province">Province of Cavite</div>
                <div class="municipality">City of Dasmariñas</div>
                <div class="barangay">BARANGAY SANTO NIÑO 1</div>
                <div class="office">Office of the Barangay Captain</div>
            </div>
            
            <div class="divider"></div>
            
            <div class="cert-title"><?php echo $cert_title; ?></div>
            
            <div class="cert-body">
                <p style="margin-bottom: 30px;">TO WHOM IT MAY CONCERN:</p>
                
                <?php if ($cert_type == 'residency'): ?>
                    <p>This is to certify that <span class="recipient-name"><?php echo strtoupper($full_name); ?></span>, 
                    of legal age, is a bonafide resident of <strong>Barangay Santo Niño 1</strong>, with residential address at 
                    <strong><?php echo $certificate['complete_address']; ?></strong>.</p>
                    
                    <p>This certification is being issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($certificate['purpose']); ?></span>.</p>
                    
                <?php elseif ($cert_type == 'indigency'): ?>
                    <p>This is to certify that <span class="recipient-name"><?php echo strtoupper($full_name); ?></span>, 
                    of legal age, is a bonafide resident of <strong>Barangay Santo Niño 1</strong>, with residential address at 
                    <strong><?php echo $certificate['complete_address']; ?></strong>.</p>
                    
                    <p>This is to certify further that the above-named person belongs to an <strong>INDIGENT FAMILY</strong> 
                    in this barangay.</p>
                    
                    <p>This certification is being issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($certificate['purpose']); ?></span>.</p>
                    
                <?php elseif ($cert_type == 'clearance'): ?>
                    <p>This is to certify that <span class="recipient-name"><?php echo strtoupper($full_name); ?></span>, 
                    of legal age, Filipino, and a resident of <strong><?php echo $certificate['complete_address']; ?></strong>, 
                    is personally known to me and to the residents of this barangay.</p>
                    
                    <p>This is to certify further that the above-named person has <strong>NO DEROGATORY RECORD</strong> 
                    on file in this barangay and is of <strong>GOOD MORAL CHARACTER</strong>.</p>
                    
                    <p>This clearance is being issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($certificate['purpose']); ?></span>.</p>
                    
                <?php elseif ($cert_type == 'business'): ?>
                    <p>This is to certify that <span class="recipient-name"><?php echo strtoupper($full_name); ?></span>, 
                    of legal age, is a bonafide resident of <strong>Barangay Santo Niño 1</strong>, with business address at 
                    <strong><?php echo $certificate['complete_address']; ?></strong>.</p>
                    
                    <p>This is to certify further that the business establishment has <strong>NO PENDING CASE</strong> 
                    or violation of any Barangay Ordinance on file in this office.</p>
                    
                    <p>This clearance is being issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($certificate['purpose']); ?></span>.</p>
                    
                <?php elseif ($cert_type == 'good_moral'): ?>
                    <p>This is to certify that <span class="recipient-name"><?php echo strtoupper($full_name); ?></span>, 
                    of legal age, is a bonafide resident of <strong>Barangay Santo Niño 1</strong>, with residential address at 
                    <strong><?php echo $certificate['complete_address']; ?></strong>.</p>
                    
                    <p>This is to certify further that based on available records in this office, the above-named person 
                    is of <strong>GOOD MORAL CHARACTER</strong> and has not been involved in any criminal or unlawful activities 
                    in this barangay.</p>
                    
                    <p>This certification is being issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($certificate['purpose']); ?></span>.</p>
                <?php endif; ?>
                
                <p style="margin-top: 30px;">Issued this <strong><?php echo date('jS'); ?></strong> day of 
                <strong><?php echo date('F Y'); ?></strong> at Barangay Santo Niño 1.</p>
            </div>
            
            <div class="signatures">
                <div class="signature-row">
                    <div class="signature-block" style="width: 100%; max-width: 300px; margin: 0 auto;">
                        <div class="signature-line">
                            <?php 
                            $captain_sig = 'assets/images/signatures/captain.svg';
                            if (file_exists($captain_sig)): 
                            ?>
                                <img src="<?php echo $captain_sig; ?>" alt="Signature" style="max-width: 200px; max-height: 40px; margin-top: 5px;">
                            <?php endif; ?>
                        </div>
                        <div class="signature-name">HON. IVAN ADAL</div>
                        <div class="signature-title">Punong Barangay</div>
                    </div>
                </div>
                
                <div style="margin-top: 50px; text-align: center;">
                    <p style="font-weight: bold; margin-bottom: 20px;">Attested by:</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 600px; margin: 0 auto;">
                        <div style="text-align: center;">
                            <div style="border-bottom: 2px solid #000; height: 50px;">
                                <?php 
                                $kag1_sig = 'assets/images/signatures/kagawad1.svg';
                                if (file_exists($kag1_sig)): 
                                ?>
                                    <img src="<?php echo $kag1_sig; ?>" alt="Signature" style="max-width: 150px; max-height: 40px; margin-top: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="signature-name">HON. YASSER ALAPAG</div>
                            <div class="signature-title">Barangay Kagawad</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 2px solid #000; height: 50px;">
                                <?php 
                                $kag2_sig = 'assets/images/signatures/kagawad2.svg';
                                if (file_exists($kag2_sig)): 
                                ?>
                                    <img src="<?php echo $kag2_sig; ?>" alt="Signature" style="max-width: 150px; max-height: 40px; margin-top: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="signature-name">HON. DAVE ABELLANOSA</div>
                            <div class="signature-title">Barangay Kagawad</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 2px solid #000; height: 50px;">
                                <?php 
                                $kag3_sig = 'assets/images/signatures/kagawad3.svg';
                                if (file_exists($kag3_sig)): 
                                ?>
                                    <img src="<?php echo $kag3_sig; ?>" alt="Signature" style="max-width: 150px; max-height: 40px; margin-top: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="signature-name">HON. HADDEN ABOGADO</div>
                            <div class="signature-title">Barangay Kagawad</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-bottom: 2px solid #000; height: 50px;">
                                <?php 
                                $kag4_sig = 'assets/images/signatures/kagawad4.svg';
                                if (file_exists($kag4_sig)): 
                                ?>
                                    <img src="<?php echo $kag4_sig; ?>" alt="Signature" style="max-width: 150px; max-height: 40px; margin-top: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="signature-name">HON. MINSEY ALFARO</div>
                            <div class="signature-title">Barangay Kagawad</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="doc-stamp">
                <p><strong>Doc. Stamp:</strong> ₱<?php echo number_format($certificate['price'], 2); ?></p>
                <p><strong>Cert. No.:</strong> <?php echo $certificate['reference_number']; ?></p>
            </div>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px; color: #666;">
        <p>Certificate Size: A4 (210mm × 297mm)</p>
        <p><a href="my-applications.php" style="color: #0b5d3c;">← Back to My Applications</a></p>
    </div>
</body>
</html>
