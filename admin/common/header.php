<?php 
include '../common/config.php'; 

// Redirect to Admin Login if not admin
if(!isset($_SESSION['admin_id'])) { 
    header("Location: login.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Dark Theme Palette matching your Dashboard
                        dark: {
                            body: '#050505',
                            card: '#111111', 
                            border: '#222222',
                            text: '#ffffff',
                            muted: '#9ca3af',
                        },
                        // Green Accent
                        primary: '#4ade80', 
                        primaryDim: 'rgba(74, 222, 128, 0.1)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Global Dark Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #050505; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4ade80; }

        body { background-color: #050505; color: #ffffff; }
    </style>
</head>
<body class="bg-[#050505] font-sans text-white antialiased">
    <div class="flex h-screen overflow-hidden">
        
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 flex flex-col h-screen overflow-hidden relative bg-[#050505]">
            
            <header class="bg-[#111111] shadow-lg p-4 flex justify-between items-center md:hidden z-20 border-b border-[#222222]">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="text-gray-400 text-xl focus:outline-none hover:text-[#4ade80] transition-colors">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
                
                </header>

            <main class="flex-1 overflow-y-auto bg-[#050505] w-full relative">
