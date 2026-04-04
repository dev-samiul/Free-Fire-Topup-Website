<?php 
include 'common/header.php'; 

// ====================================================
// INITIALIZE VARIABLES
// ====================================================
$editMode = false;
$editId = 0;
$currLink = '';
$currImg = '';

// Handle "Edit" Request
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM sliders WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $currLink = $row['link'];
        $currImg = $row['image'];
    }
}

// ====================================================
// HANDLE SAVE (ADD / UPDATE)
// ====================================================
if(isset($_POST['save_slider'])) {
    $link = $conn->real_escape_string($_POST['link']);
    $finalImage = $editMode ? $currImg : ''; 

    // Image Upload Logic
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "slider_" . time() . "." . $ext;
            if(move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $newFilename)) {
                $finalImage = "uploads/" . $newFilename;
            }
        }
    }

    if(empty($finalImage)) {
        echo "<script>alert('Image is required!');</script>";
    } else {
        if($editMode) {
            $uid = (int)$_POST['update_id'];
            $conn->query("UPDATE sliders SET image='$finalImage', link='$link' WHERE id=$uid");
        } else {
            $conn->query("INSERT INTO sliders (image, link) VALUES ('$finalImage', '$link')");
        }
        echo "<script>window.location='sliders.php';</script>";
    }
}

// ====================================================
// HANDLE DELETE
// ====================================================
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM sliders WHERE id=$id");
    echo "<script>window.location='sliders.php';</script>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-body: #050505;
        --bg-card: #111111;
        --bg-input: #1a1a1a;
        --border: #222222;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af;
        --accent: #eab308;
        --red: #ef4444;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    .container-fluid { padding: 20px; max-width: 100%; }

    /* HEADER */
    .page-header { margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 700; margin-bottom: 10px; }
    .btn-new {
        background: var(--accent); color: #000; padding: 10px 20px;
        border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer;
    }

    /* CARD */
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    /* FILTER BAR */
    .filter-bar {
        padding: 15px; border-bottom: 1px solid var(--border);
        display: flex; gap: 10px; align-items: center;
    }
    .search-box {
        flex: 1; background: var(--bg-input); border: 1px solid var(--border);
        border-radius: 8px; display: flex; align-items: center; padding: 10px 12px;
    }
    .search-box input {
        background: transparent; border: none; outline: none;
        color: #fff; width: 100%; font-size: 14px; margin-left: 10px;
    }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 700px; }
    
    th {
        text-align: left; padding: 15px 20px;
        color: var(--text-secondary); font-size: 13px; font-weight: 600;
        border-bottom: 1px solid var(--border); background: #161616;
    }
    
    td {
        padding: 15px 20px; border-bottom: 1px solid var(--border);
        font-size: 14px; color: #fff; vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* THUMBNAIL */
    .slider-thumb {
        width: 100px; height: 40px; border-radius: 4px; object-fit: cover;
        border: 1px solid #333; background: #222;
    }

    /* ACTIONS */
    .btn-icon { color: var(--text-secondary); cursor: pointer; margin-left: 10px; }
    .btn-icon:hover { color: #fff; }
    .btn-edit { color: var(--accent); }
    .btn-del { color: var(--red); }

    /* FOOTER */
    .table-footer { padding: 15px; display: flex; justify-content: center; border-top: 1px solid var(--border); }
    .limit-select { background: #1a1a1a; color: #fff; border: 1px solid #333; padding: 5px 12px; border-radius: 6px; }

    /* MODAL */
    .modal {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.8); z-index: 100;
        align-items: center; justify-content: center;
    }
    .modal-content {
        background: var(--bg-card); border: 1px solid var(--border);
        width: 90%; max-width: 500px; border-radius: 12px; padding: 25px;
    }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 8px; }
    .form-input { 
        width: 100%; background: var(--bg-input); border: 1px solid var(--border);
        padding: 12px; border-radius: 8px; color: #fff; outline: none;
    }
    .btn-save {
        background: var(--accent); color: #000; padding: 12px; border-radius: 8px;
        font-weight: 700; width: 100%; border: none; cursor: pointer;
    }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Sliders</h1>
        <button onclick="openModal('sliderModal')" class="btn-new">New slider</button>
    </div>

    <div class="table-card">
        
        <div class="filter-bar">
            <i data-feather="arrow-up-down" style="color:#666; width:16px;"></i>
            <div class="search-box">
                <i data-feather="search" style="color: #666; width: 18px;"></i>
                <input type="text" placeholder="Search (URL)">
            </div>
            <i data-feather="columns" style="color: #666; width: 20px;"></i>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" style="accent-color:#eab308;"></th>
                        <th>URL <i data-feather="chevron-down" style="width:12px;"></i></th>
                        <th style="text-align:right;">Image <i data-feather="chevron-down" style="width:12px;"></i></th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sliders = $conn->query("SELECT * FROM sliders ORDER BY id DESC");
                    if($sliders && $sliders->num_rows > 0):
                        while($row = $sliders->fetch_assoc()): 
                            $linkText = !empty($row['link']) ? $row['link'] : 'No Link Assigned';
                    ?>
                    <tr>
                        <td><input type="checkbox" style="accent-color:#eab308;"></td>
                        <td style="color:#aaa; font-size:13px;"><?php echo htmlspecialchars($linkText); ?></td>
                        <td style="text-align:right;">
                            <img src="../<?php echo $row['image']; ?>" class="slider-thumb">
                        </td>
                        <td style="text-align:right;">
                            <a href="javascript:void(0)" onclick='openEditModal(<?php echo json_encode($row); ?>)' class="btn-icon btn-edit"><i data-feather="edit-2" style="width:14px;"></i></a>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-icon btn-del"><i data-feather="trash" style="width:14px;"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:#666;">No sliders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <select class="limit-select">
                <option>10</option>
                <option>25</option>
            </select>
        </div>
    </div>

</div>

<div id="sliderModal" class="modal">
    <div class="modal-content">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:20px;">Manage Slider</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="save_slider" value="1">
            <input type="hidden" name="update_id" id="edit_id">
            
            <div class="form-group">
                <label class="form-label">Redirect URL (Optional)</label>
                <input type="text" name="link" id="edit_link" class="form-input" placeholder="https://...">
            </div>

            <div class="form-group">
                <label class="form-label">Slider Image</label>
                <input type="file" name="image" class="form-input" accept="image/*">
                <p style="font-size:10px; color:#666; margin-top:5px;">Required for new sliders.</p>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" onclick="closeModal('sliderModal')" style="flex:1; background:transparent; border:1px solid #333; color:#fff; padding:12px; border-radius:8px;">Cancel</button>
                <button type="submit" style="flex:1;" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    feather.replace();

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
        // Reset form for add mode
        document.getElementById('edit_id').value = '';
        document.getElementById('edit_link').value = '';
    }

    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_link').value = data.link;
        openModal('sliderModal');
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete?',
            text: "This cannot be undone.",
            icon: 'warning',
            background: '#1a1a1a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#333',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?del=' + id;
            }
        });
    }
</script>
