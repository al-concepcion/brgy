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
    die('Invalid ID application');
}

// Get ID application details - verify it belongs to the logged-in user
try {
    $stmt = $conn->prepare("SELECT * FROM id_applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        die('ID application not found or access denied');
    }
    
    // Only allow viewing if status is Ready or Completed
    if (!in_array($application['status'], ['Ready for Pickup', 'Ready for Delivery', 'Completed'])) {
        die('ID is not yet ready for printing');
    }
} catch(PDOException $e) {
    die('Error retrieving ID application');
}

$full_name = $application['first_name'] . ' ' . 
             ($application['middle_name'] ? $application['middle_name'] . ' ' : '') . 
             $application['last_name'];

// Format date of birth safely - column name is birth_date in database
$date_of_birth = isset($application['birth_date']) && !empty($application['birth_date']) 
    ? date('Y/m/d', strtotime($application['birth_date'])) 
    : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print ID - <?php echo $application['reference_number']; ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: 85.6mm 53.98mm;
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
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .id-card-wrapper {
            width: 85.6mm;
            height: 53.98mm;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .id-card {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0b5d3c 0%, #12754a 100%);
            position: relative;
            overflow: hidden;
        }
        
        .id-header {
            background: rgba(255,255,255,0.95);
            padding: 5px 10px;
            text-align: center;
        }
        
        .id-header h3 {
            font-size: 10px;
            color: #0b5d3c;
            margin: 2px 0;
            font-weight: bold;
        }
        
        .id-header h2 {
            font-size: 12px;
            color: #12754a;
            margin: 2px 0;
            font-weight: bold;
        }
        
        .id-content {
            padding: 8px 10px;
            display: flex;
            gap: 8px;
            position: relative;
            z-index: 2;
        }
        
        .photo-section {
            flex-shrink: 0;
        }
        
        .photo-box {
            width: 70px;
            height: 70px;
            background: white;
            border: 2px solid #ffd700;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-placeholder {
            color: #ccc;
            font-size: 10px;
            text-align: center;
        }
        
        .info-section {
            flex-grow: 1;
            color: white;
        }
        
        .info-row {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-size: 7px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 9px;
            font-weight: bold;
            margin-top: 1px;
        }
        
        .id-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            padding: 4px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .id-number {
            font-size: 8px;
            color: #0b5d3c;
            font-weight: bold;
        }
        
        .validity {
            font-size: 7px;
            color: #666;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 24px;
            opacity: 0.05;
            color: white;
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
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
        
        .info-text {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }
    </style>
</head>
<body>
    <div style="max-width: 400px;">
        <button onclick="window.print()" class="print-button no-print">Print ID Card</button>
        
        <div class="id-card-wrapper">
            <div class="id-card">
                <div class="watermark">BARANGAY SANTO NIÑO 1</div>
                
                <div class="id-header">
                    <h3>Republic of the Philippines</h3>
                    <h2>BARANGAY SANTO NIÑO 1</h2>
                    <h3>Dasmariñas City, Cavite</h3>
                </div>
                
                <div class="id-content">
                    <div class="photo-section">
                        <div class="photo-box">
                            <?php if (!empty($application['id_photo']) && file_exists('uploads/id_applications/' . $application['id_photo'])): ?>
                                <img src="uploads/id_applications/<?php echo $application['id_photo']; ?>" alt="Photo">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fas fa-user" style="font-size: 30px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-row">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo strtoupper($full_name); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo strtoupper($application['complete_address']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?php echo $date_of_birth; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Contact</div>
                            <div class="info-value"><?php echo $application['contact_number']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="id-footer">
                    <div class="id-number">ID No: <?php echo $application['reference_number']; ?></div>
                    <div class="validity">Valid for 2 years</div>
                </div>
                
                <div style="position: absolute; bottom: 25px; right: 10px; text-align: center; z-index: 10;">
                    <?php 
                    $captain_sig = 'assets/images/signatures/captain.svg';
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
        
        <div class="info-text no-print">
            <p><strong>ID Card Size:</strong> CR80 (85.6mm × 53.98mm)</p>
            <p style="margin-top: 10px;"><a href="my-applications.php" style="color: #0b5d3c;">← Back to My Applications</a></p>
        </div>
    </div>
</body>
</html>
