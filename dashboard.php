<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Restrict to scholars only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Fetch scholar's info
$sql = "SELECT first_name, middle_name, last_name, course, year_level, scholarship_type, profile_pic FROM scholars WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$scholar = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$scholar) die("Scholar details not found.");

// Get scholar ID
$scholar_id_stmt = $conn->prepare("SELECT id FROM scholars WHERE user_id = ?");
$scholar_id_stmt->execute([$user_id]);
$scholar_row = $scholar_id_stmt->fetch(PDO::FETCH_ASSOC);
$scholar_id = $scholar_row['id'] ?? null;

// Fetch uploaded docs with status (includes LOA if it exists)
$uploaded = [];
if ($scholar_id) {
    $doc_stmt = $conn->prepare("SELECT document_type, file_path, status FROM documents WHERE scholar_id = ?");
    $doc_stmt->execute([$scholar_id]);
    foreach ($doc_stmt->fetchAll(PDO::FETCH_ASSOC) as $doc) {
        $uploaded[$doc['document_type']] = [
            'file_path' => $doc['file_path'],
            'status'    => $doc['status']
        ];
    }
}

// Required docs from admin (fetch all fields for deadline and is_permanent)
$requirements = $conn->query("SELECT * FROM requirements ORDER BY is_permanent DESC, tag ASC")->fetchAll(PDO::FETCH_ASSOC);
// Get group deadline for permanent requirements
$groupDeadline = null;
foreach ($requirements as $r) {
    if (isset($r['is_permanent']) && $r['is_permanent']) {
        $groupDeadline = $r['deadline'];
        break;
    }
}

// Check if LOA is toggled on for this scholar
$loa_required = false;
if ($scholar_id) {
    $special_stmt = $conn->prepare("SELECT required FROM special_cases WHERE scholar_id = ? AND case_type = 'LOA' LIMIT 1");
    $special_stmt->execute([$scholar_id]);
    $special_case = $special_stmt->fetch(PDO::FETCH_ASSOC);
    $loa_required = $special_case && $special_case['required'] == 1;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Track if all credentials are uploaded and approved
$allUploaded = true;
foreach ($requirements as $req) {
    $type = $req['document_type'];
    $docInfo = $uploaded[$type] ?? null;
    $isUploaded = $docInfo && file_exists($docInfo['file_path']);
    $status = $docInfo['status'] ?? null;
    if (!$isUploaded || strtolower($status) !== 'approved') $allUploaded = false;
}

// Deadline helper
function getDeadlineStatus($deadline) {
    if (!$deadline) return '';
    $today = date('Y-m-d');
    $diff = strtotime($deadline) - strtotime($today);
    if ($diff < 0) return '<span class="text-red-600 font-bold">(Overdue!)</span>';
    if ($diff <= 259200) return '<span class="text-yellow-600 font-bold">(Due soon!)</span>';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Scholar Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('pictures/iccbackground.png') no-repeat center center fixed;
            background-size: cover;
            background-blend-mode: lighten;
            background-color: rgba(255, 255, 255, 0.85);
            overflow-x: hidden;
        }
        .dropdown-menu { display: none; }
        .dropdown-menu.show { display: block; }
        .modal { display: none; }
        .modal.show { display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1050; }
        .modal-content { transform: translateY(0); transition: transform 0.3s ease-out; }
        .modal.fade .modal-content { transform: translateY(-50px); }
        .modal.show .modal-content { transform: translateY(0); }
        .modal-content { max-width: 32rem; width: 100%; }
        @media (max-width: 640px) { .modal-content { margin: 1rem; } }
        .upload-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .upload-card:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .upload-button { transition: background-color 0.3s ease, transform 0.2s ease; }
        .upload-button:hover:not(:disabled) { transform: scale(1.05); }
    </style>
</head>
<body class="font-sans min-h-screen flex flex-col">
<div class="absolute top-4 left-4 z-10">
    <img src="pictures/ICC_New-Logo_2022.jpg" alt="ICC Logo" class="w-16 h-16 object-contain rounded-full bg-white shadow-md">
</div>
<header class="bg-gradient-to-r from-blue-600 to-blue-400 text-white p-6 md:p-8 text-center shadow-lg">
    <h1 class="text-2xl md:text-3xl font-extrabold flex items-center justify-center">
        <i class="fa fa-graduation-cap mr-2"></i> Scholar Dashboard
    </h1>
    <p class="text-sm md:text-base text-white opacity-90 mt-1">
        Welcome, <?= htmlspecialchars(trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''))) ?>
    </p>
</header>
<div class="absolute top-4 right-4 md:top-6 md:right-6 z-[1050]">
    <button class="flex items-center text-white" onclick="toggleDropdown()">
        <?php if (!empty($scholar['profile_pic']) && file_exists($scholar['profile_pic'])): ?>
            <img src="<?= htmlspecialchars($scholar['profile_pic']) ?>" class="w-10 h-10 object-cover rounded-full">
        <?php else: ?>
            <i class="fa fa-user-circle text-3xl"></i>
        <?php endif; ?>
    </button>
    <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-none z-50">
        <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="profile.php"><i class="fa fa-user mr-2"></i> Profile</a></li>
        <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="settings.php"><i class="fa fa-cog mr-2"></i> Settings</a></li>
        <li><hr class="border-gray-200 my-1"></li>
        <li><a class="block px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white flex items-center" href="logout.php"><i class="fa fa-sign-out-alt mr-2"></i> Logout</a></li>
    </ul>
</div>
<main class="container mx-auto my-6 md:my-8 px-4 flex justify-center">
    <div class="w-full max-w-4xl">
        <!-- Required Credentials (badge view) - kept commented, unchanged -->
        <!--
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 bg-gradient-to-r from-blue-50 to-gray-50">
                <h4 class="text-xl font-bold text-blue-600 mb-4 flex items-center"><i class="fa fa-list-check mr-2"></i> Required Credentials</h4>
                <div class="flex flex-wrap gap-2 mt-3" id="requirements-list">
                    <?php foreach ($requirements as $req):
                        $type = $req['document_type'];
                        $docInfo = $uploaded[$type] ?? null;
                        $isUploaded = $docInfo && file_exists($docInfo['file_path']);
                        $status = $docInfo['status'] ?? null;
                        $color = 'gray';
                        $icon = 'fa-file';
                        $label = 'Not uploaded';
                        if ($isUploaded) {
                            switch (strtolower($status)) {
                                case 'approved': $color='green'; $icon='fa-check-circle'; $label='Approved'; break;
                                case 'rejected': $color='red'; $icon='fa-times-circle'; $label='Rejected'; break;
                                case 'pending': default: $color='yellow'; $icon='fa-hourglass-half'; $label='Pending'; break;
                            }
                        }
                    ?>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-<?= $color ?>-100 text-<?= $color ?>-700 border border-<?= $color ?>-300" data-doc-type="<?= htmlspecialchars($type, ENT_QUOTES) ?>">
                            <i class="fa <?= $icon ?> mr-1 doc-icon"></i>
                            <span class="doc-type"><?= htmlspecialchars($type) ?></span>
                            <span class="ml-2 text-xs font-semibold req-status"><?= $label ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php if ($allUploaded): ?>
                    <div class="mt-4 text-center text-green-600 bg-green-50 border border-green-200 rounded-lg p-2 text-sm">
                        <i class="fa fa-check-circle mr-1"></i> All credentials submitted and approved.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        -->

        <!-- Upload Credentials -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <h4 class="text-xl font-bold text-blue-600 mb-6 flex items-center justify-center">
                <i class="fa fa-upload mr-2"></i> Upload Credentials
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- ===== Independent LOA Upload Card (SHOWN ONLY IF TOGGLED ON) ===== -->
                    <?php if ($loa_required): ?>
                    <?php
                    $loaInfo        = $uploaded['LOA'] ?? null;
                    $isUploadedLOA  = $loaInfo && file_exists($loaInfo['file_path']);
                    $loaStatus      = $loaInfo['status'] ?? null;
                    $loaColor = 'gray';
                    $loaIcon  = 'fa-file';
                    $loaLabel = 'Not uploaded';
                    $canUploadLOA = true;

                    if ($isUploadedLOA) {
                        switch (strtolower($loaStatus)) {
                            case 'approved': $loaColor='green';  $loaIcon='fa-check-circle';   $loaLabel='Approved'; $canUploadLOA=false; break;
                            case 'rejected': $loaColor='red';    $loaIcon='fa-times-circle';   $loaLabel='Rejected'; break;
                            case 'pending':  default:            $loaColor='yellow'; $loaIcon='fa-hourglass-half'; $loaLabel='Pending';  break;
                        }
                    }
                    ?>
                    <div class="upload-card bg-<?= $loaColor ?>-50 rounded-lg shadow-sm p-4 flex flex-col items-center">
                        <div class="flex items-center mb-3 w-full justify-center">
                            <span class="text-xl mr-2"><i class="fa <?= $loaIcon ?> text-<?= $loaColor ?>-600"></i></span>
                            <span class="font-semibold text-center">Leave of Absence (LOA)</span>
                        </div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?= $loaColor ?>-600 text-white mb-3">
                            <?= $loaLabel ?>
                        </div>
                        <div class="flex gap-3 w-full justify-center">
                            <?php if ($isUploadedLOA): ?>
                                <a href="<?= htmlspecialchars($loaInfo['file_path']) ?>" target="_blank"
                                   class="upload-button flex-1 text-center bg-transparent border border-<?= $loaColor ?>-600 text-<?= $loaColor ?>-600 px-4 py-2 rounded-full hover:bg-<?= $loaColor ?>-600 hover:text-white transition text-sm">
                                    <i class="fa fa-eye mr-1"></i> View
                                </a>
                                <button onclick="deleteCredential('LOA', '<?= $_SESSION['csrf_token'] ?>')"
                                        class="upload-button flex-1 text-center bg-transparent border border-red-600 text-red-600 px-4 py-2 rounded-full hover:bg-red-600 hover:text-white transition text-sm">
                                    <i class="fa fa-times-circle mr-1"></i> Unsubmit
                                </button>
                            <?php else: ?>
                                <button <?= $canUploadLOA ? '' : 'disabled class=\"bg-gray-300 text-gray-700 cursor-not-allowed\"' ?>
                                        class="upload-button flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition text-sm"
                                        onclick="showModal('LOA')">
                                    <i class="fa fa-upload mr-1"></i> Upload
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- ===== /LOA Card ===== -->
                    <?php endif; ?>

                <!-- ===== Existing Requirements Cards ===== -->
                <?php foreach ($requirements as $req):
                    $type     = $req['document_type'];
                    // Safety: skip if admin coincidentally added LOA in requirements.
                    if (strtoupper($type) === 'LOA') { continue; }

                    $docInfo  = $uploaded[$type] ?? null;
                    $isUploaded = $docInfo && file_exists($docInfo['file_path']);
                    $status   = $docInfo['status'] ?? null;

                    $color = 'gray'; $icon = 'fa-file'; $label = 'Not uploaded'; $canUpload = true;
                    if ($isUploaded) {
                        switch (strtolower($status)) {
                            case 'approved': $color='green';  $icon='fa-check-circle';   $label='Approved'; $canUpload=false; break;
                            case 'rejected': $color='red';    $icon='fa-times-circle';   $label='Rejected'; break;
                            case 'pending':  default:          $color='yellow'; $icon='fa-hourglass-half'; $label='Pending';  break;
                        }
                    }
                    // Deadline logic
                    $deadline = ($req['is_permanent'] ?? 0) ? $groupDeadline : $req['deadline'];
                    $deadline_display = $deadline ? date('M d, Y', strtotime($deadline)) : '-';
                    $deadline_status = '';
                    if ($deadline) {
                        $today = date('Y-m-d');
                        $diff = strtotime($deadline) - strtotime($today);
                        if ($diff < 0) $deadline_status = '<span class="text-red-600 font-bold">(Overdue!)</span>';
                        elseif ($diff <= 3*24*60*60) $deadline_status = '<span class="text-yellow-600 font-bold">(Due soon!)</span>';
                    }
                ?>
                    <div class="upload-card bg-<?= $color ?>-50 rounded-lg shadow-sm p-4 flex flex-col items-center">
                        <div class="flex items-center mb-3 w-full justify-center">
                            <span class="text-xl mr-2"><i class="fa <?= $icon ?> text-<?= $color ?>-600"></i></span>
                            <span class="font-semibold text-center"><?= htmlspecialchars($type) ?></span>
                        </div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?= $color ?>-600 text-white mb-3">
                            <?= $label ?>
                        </div>
                        <div class="mb-2 text-xs text-gray-700">
                            <span class="font-semibold">Deadline:</span> <?= $deadline_display ?> <?= $deadline_status ?>
                        </div>
                        <div class="flex gap-3 w-full justify-center">
                            <?php if ($isUploaded): ?>
                                <a href="<?= htmlspecialchars($docInfo['file_path']) ?>" target="_blank"
                                   class="upload-button flex-1 text-center bg-transparent border border-<?= $color ?>-600 text-<?= $color ?>-600 px-4 py-2 rounded-full hover:bg-<?= $color ?>-600 hover:text-white transition text-sm">
                                    <i class="fa fa-eye mr-1"></i> View
                                </a>
                                <button onclick="deleteCredential('<?= htmlspecialchars($type, ENT_QUOTES) ?>', '<?= $_SESSION['csrf_token'] ?>')"
                                        class="upload-button flex-1 text-center bg-transparent border border-red-600 text-red-600 px-4 py-2 rounded-full hover:bg-red-600 hover:text-white transition text-sm">
                                    <i class="fa fa-times-circle mr-1"></i> Unsubmit
                                </button>
                            <?php else: ?>
                                <button <?= $canUpload ? '' : 'disabled class=\"bg-gray-300 text-gray-700 cursor-not-allowed\"' ?>
                                        class="upload-button flex-1 text-center bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition text-sm"
                                        onclick="showModal('<?= htmlspecialchars($type, ENT_QUOTES) ?>')">
                                    <i class="fa fa-upload mr-1"></i> Upload
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <!-- ===== /Existing Requirements Cards ===== -->
            </div>
        </div>
    </div>
</main>

<footer class="text-center text-gray-500 mt-8">&copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.</footer>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-content max-w-md w-full bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <h5 class="text-xl font-bold text-blue-600"><i class="fa fa-upload mr-2"></i> Upload Document</h5>
            <button type="button" class="text-gray-600 hover:text-gray-800 transition" onclick="hideModal()">
                <i class="fa fa-times text-xl"></i>
            </button>
        </div>
        <form id="uploadForm" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="ajax_upload" value="1">
            <input type="hidden" name="document_type" id="docTypeInput">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Document</label>
                <div class="relative">
                    <input type="file" name="document" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" required>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition font-semibold text-sm flex items-center justify-center">
                <i class="fa fa-upload mr-2"></i> Upload Document
            </button>
        </form>
        <div id="uploadStatus" class="mt-4 text-center"></div>
    </div>
</div>

<script>
function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown-menu');
    dropdown.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.dropdown-menu');
    const toggleBtn = document.querySelector('button[onclick="toggleDropdown()"]');
    if (dropdown && !dropdown.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

function showModal(docType) {
    document.getElementById('docTypeInput').value = docType;
    document.getElementById('uploadModal').classList.add('show');
}
function hideModal() {
    document.getElementById('uploadModal').classList.remove('show');
    document.getElementById('uploadForm').reset();
    document.getElementById('uploadStatus').innerHTML = '';
}

function deleteCredential(docType, token) {
    if (confirm("Unsubmit this document?")) {
        fetch('delete_credential.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `document_type=${encodeURIComponent(docType)}&csrf_token=${encodeURIComponent(token)}`
        })
        .then(res => res.json())
        .then(data => { if (data.success) location.reload(); else alert(data.message || 'Failed to unsubmit.'); })
        .catch(() => { alert('An error occurred.'); });
    }
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch('upload.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        let statusDiv = document.getElementById('uploadStatus');
        if (data.success) {
            statusDiv.innerHTML = '<div class="text-green-600 bg-green-50 border border-green-200 rounded-lg p-2 text-sm"><i class="fa fa-check-circle mr-1"></i>'+data.message+'</div>';
            setTimeout(() => { hideModal(); location.reload(); }, 1500);
        } else {
            statusDiv.innerHTML = '<div class="text-red-600 bg-red-50 border border-red-200 rounded-lg p-2 text-sm"><i class="fa fa-times-circle mr-1"></i>'+data.message+'</div>';
        }
    })
    .catch(() => {
        document.getElementById('uploadStatus').innerHTML = '<div class="text-red-600 bg-red-50 border border-red-200 rounded-lg p-2 text-sm"><i class="fa fa-times-circle mr-1"></i>Server error.</div>';
    });
});
</script>

<!-- Optional live status polling (unchanged) -->
<script>
const STATUS_MAP = {
    'approved': { color: 'green', icon: 'fa-check-circle', label: 'Approved' },
    'rejected': { color: 'red', icon: 'fa-times-circle', label: 'Rejected' },
    'pending':  { color: 'yellow', icon: 'fa-hourglass-half', label: 'Pending' }
};
function normalizeStatus(s) { return s ? String(s).toLowerCase() : null; }

function updateRequirementsUI(documents) {
    const reqItems = document.querySelectorAll('#requirements-list [data-doc-type]');
    let allApproved = true;
    reqItems.forEach(el => {
        const type = el.getAttribute('data-doc-type');
        const statusSpan = el.querySelector('.req-status');
        const iconEl = el.querySelector('.doc-icon');
        const doc = documents[type] || null;
        let status = doc && doc.status ? normalizeStatus(doc.status) : null;
        let meta = STATUS_MAP['pending'];
        if (status && STATUS_MAP[status]) meta = STATUS_MAP[status];

        el.className = el.className
            .replace(/bg-[a-z]+-\d{2,3}/g, '')
            .replace(/text-[a-z]+-\d{2,3}/g, '')
            .replace(/border border-[a-z]+-\d{2,3}/g, '');
        el.classList.add(`bg-${meta.color}-100`, `text-${meta.color}-700`, `border`, `border-${meta.color}-300`);
        if (iconEl) iconEl.className = `fa ${meta.icon} mr-1 doc-icon`;
        if (statusSpan) statusSpan.textContent = meta.label;
        if (status !== 'approved') allApproved = false;
    });

    const banner = document.querySelector('.mt-4.text-center.text-green-600');
    if (allApproved) {
        if (!banner) {
            const container = document.querySelector('.lg\\:col-span-5 > .bg-white');
            if (container) {
                const div = document.createElement('div');
                div.className = 'mt-4 text-center text-green-600 bg-green-50 border border-green-200 rounded-lg p-2 text-sm';
                div.innerHTML = '<i class="fa fa-check-circle mr-1"></i> All credentials submitted and approved.';
                container.appendChild(div);
            }
        }
    } else {
        if (banner) banner.remove();
    }
}

async function pollStatuses() {
    try {
        const res = await fetch('ajax_get_documents_status.php', { cache: 'no-store' });
        const data = await res.json();
        if (!data.success) return;
        updateRequirementsUI(data.documents || {});
    } catch (e) {
        console.error('Status poll failed', e);
    }
}
// Uncomment if you use the badge section.
 pollStatuses();
 setInterval(pollStatuses, 6000);
</script>
</body>
</html>
