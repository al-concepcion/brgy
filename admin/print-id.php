<?php
require_once '../includes/config.php';
require_login();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die('Invalid ID application');
}

// Get application details
try {
    $stmt = $conn->prepare("SELECT * FROM id_applications WHERE id = ?");
    $stmt->execute([$id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        die('Application not found');
    }
} catch(PDOException $e) {
    die('Error retrieving application');
}

// Get user photo
$photo_path = '../uploads/id_applications/' . $application['id_photo'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barangay ID - <?php echo $application['reference_number']; ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: 85.6mm 53.98mm;
                margin: 0;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        
        .id-card-wrapper {
            width: 85.6mm;
            height: 53.98mm;
            margin: 0 auto;
            position: relative;
        }
        
        .id-card {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0b5d3c 0%, #12754a 100%);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .id-header {
            background: rgba(255,255,255,0.95);
            padding: 8px 12px;
            text-align: center;
            border-bottom: 3px solid #ffd700;
        }
        
        .id-header h1 {
            font-size: 14px;
            color: #0b5d3c;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .id-header p {
            font-size: 8px;
            color: #555;
            margin: 2px 0 0 0;
        }
        
        .id-body {
            display: flex;
            padding: 10px;
            gap: 10px;
            color: white;
        }
        
        .photo-section {
            flex-shrink: 0;
        }
        
        .photo-frame {
            width: 70px;
            height: 70px;
            border: 3px solid #ffd700;
            border-radius: 4px;
            overflow: hidden;
            background: white;
        }
        
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .info-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .info-row {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-size: 7px;
            opacity: 0.8;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 10px;
            font-weight: bold;
            margin-top: 1px;
        }
        
        .id-number {
            font-size: 11px;
            font-weight: bold;
            color: #ffd700;
            margin-top: 5px;
        }
        
        .id-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.3);
            padding: 4px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 7px;
            color: white;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 40px;
            opacity: 0.05;
            color: white;
            font-weight: bold;
            pointer-events: none;
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
    <button onclick="window.print()" class="print-button no-print">Print ID Card</button>
    
    <div class="id-card-wrapper">
        <div class="id-card">
            <div class="watermark">OFFICIAL</div>
            
            <div class="id-header">
                <h1>Barangay Santo Niño 1</h1>
                <p>Identification Card</p>
            </div>
            
            <div class="id-body">
                <div class="photo-section">
                    <div class="photo-frame">
                        <?php if ($application['id_photo'] && file_exists($photo_path)): ?>
                            <img src="<?php echo $photo_path; ?>" alt="ID Photo">
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #ddd; color: #666; font-size: 10px;">
                                NO PHOTO
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Name</div>
                        <div class="info-value">
                            <?php 
                            echo strtoupper($application['first_name'] . ' ' . 
                                 ($application['middle_name'] ? substr($application['middle_name'], 0, 1) . '. ' : '') . 
                                 $application['last_name'] . 
                                 ($application['suffix'] ? ' ' . $application['suffix'] : '')); 
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Address</div>
                        <div class="info-value" style="font-size: 8px;">
                            <?php echo strtoupper(substr($application['complete_address'], 0, 50)); ?>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            <?php echo strtoupper(date('Y/m/d', strtotime($application['birth_date']))); ?>
                        </div>
                    </div>
                    
                    <div class="id-number">
                        ID NO: <?php echo $application['reference_number']; ?>
                    </div>
                </div>
            </div>
            
            <div class="id-footer">
                <span>Issued: <?php echo date('m/d/Y'); ?></span>
                <span>Valid Until: <?php echo date('m/d/Y', strtotime('+2 years')); ?></span>
            </div>
            
            <div style="position: absolute; bottom: 25px; right: 10px; text-align: center; z-index: 10;">
                <?php 
                $captain_sig = '../assets/images/signatures/captain.svg';
                if (file_exists($captain_sig)): 
                ?>
                    <img src="<?php echo $captain_sig; ?>" alt="Signature" style="max-width: 55px; max-height: 18px; display: block; margin: 0 auto 2px;">
                <?php else: ?>
                    <div style="width: 55px; height: 18px; border-bottom: 1px solid rgba(255,255,255,0.5); margin-bottom: 2px;"></div>
                <?php endif; ?>
                <div style="font-size: 7px; color: white; font-weight: bold; line-height: 1.3; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Hon. Ivan Adal</div>
                <div style="font-size: 6px; color: white; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">Punong Barangay</div>
            </div>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px; color: #666;">
        <p>ID Card Size: 85.6mm × 53.98mm (Standard CR80)</p>
        <p><a href="id-applications.php" style="color: #1e3c72;">← Back to Applications</a></p>
    </div>
</body>
</html>
