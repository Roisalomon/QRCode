<?php
// Script de traitement pour l'upload d'image
$uploadedImageUrl = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Extensions autorisées
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            // Obtenir l'URL complète
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $scriptPath = dirname($_SERVER['PHP_SELF']);
            $scriptPath = rtrim($scriptPath, '/\\');
            $uploadedImageUrl = $protocol . "://" . $domain . $scriptPath . "/" . $targetFilePath;
        } else {
            $uploadError = "Erreur lors du téléchargement du fichier.";
        }
    } else {
        $uploadError = "Seuls les fichiers JPG, JPEG, PNG, GIF et WEBP sont autorisés.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de QR Code Instantané</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- CDN Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-full flex flex-col justify-between font-sans bg-slate-900">

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <header class="text-center mb-10">
            <div class="inline-flex items-center justify-center p-3 bg-indigo-600/20 rounded-2xl mb-4 text-indigo-400 border border-indigo-500/30 shadow-lg shadow-indigo-500/10">
                <i data-lucide="qr-code" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Générateur de QR Code Pro</h1>
            <p class="text-slate-400 mt-2 text-sm md:text-base">Générez des QR codes instantanés pour vos liens, textes ou vos images en quelques clics.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Left Side: Input Panel -->
            <div class="md:col-span-7 bg-slate-800/80 backdrop-blur-md rounded-2xl p-6 border border-slate-700/60 shadow-xl">
                
                <!-- Navigation Tabs -->
                <div class="flex border-b border-slate-700 mb-6 space-x-4">
                    <button id="tab-text-btn" onclick="switchTab('text')" class="pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-indigo-500 text-indigo-400 transition-colors">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span>Lien / Texte</span>
                    </button>
                    <button id="tab-image-btn" onclick="switchTab('image')" class="pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition-colors">
                        <i data-lucide="image" class="w-4 h-4"></i>
                        <span>Héberger une Image</span>
                    </button>
                </div>

                <!-- Section Text / URL Input -->
                <div id="section-text" class="space-y-4">
                    <label for="text-input" class="block text-sm font-medium text-slate-300">Contenu à encoder</label>
                    <textarea id="text-input" rows="4" placeholder="Saisissez une URL (ex: https://mon-site.com) ou du texte..." class="w-full bg-slate-900/90 border border-slate-700 rounded-xl p-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm"></textarea>
                    
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Couleur du QR</label>
                            <input type="color" id="color-dark" value="#000000" class="w-full h-10 bg-slate-900 border border-slate-700 rounded-lg cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Arrière-plan</label>
                            <input type="color" id="color-light" value="#ffffff" class="w-full h-10 bg-slate-900 border border-slate-700 rounded-lg cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- Section Image Upload -->
                <div id="section-image" class="hidden space-y-4">
                    <p class="text-xs text-slate-400">Téléversez votre image pour générer une URL accessible via le QR code.</p>
                    
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="border-2 border-dashed border-slate-700 hover:border-indigo-500/60 rounded-xl p-6 text-center transition-colors bg-slate-900/40">
                            <i data-lucide="upload-cloud" class="w-10 h-10 mx-auto text-slate-500 mb-2"></i>
                            <label for="image" class="cursor-pointer text-sm text-indigo-400 font-medium hover:underline">Cliquez pour choisir un fichier</label>
                            <input type="file" name="image" id="image" class="hidden" onchange="showFileName(this)">
                            <p id="file-name" class="text-xs text-slate-500 mt-2">Format supporté: PNG, JPG, GIF, WEBP</p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2.5 px-4 rounded-xl transition flex items-center justify-center space-x-2 text-sm shadow-lg shadow-indigo-600/20">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Téléverser & Générer QR</span>
                        </button>
                    </form>

                    <?php if ($uploadError): ?>
                        <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-xs flex items-center space-x-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($uploadError) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Side: Preview & Download Panel -->
            <div class="md:col-span-5 bg-slate-800/80 backdrop-blur-md rounded-2xl p-6 border border-slate-700/60 shadow-xl flex flex-col items-center justify-center min-h-[380px]">
                <h2 class="text-sm font-medium text-slate-400 mb-4 flex items-center space-x-2">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span>Aperçu en temps réel</span>
                </h2>

                <!-- Container du QR code -->
                <div class="p-4 bg-white rounded-xl shadow-2xl mb-6 flex items-center justify-center min-w-[200px] min-h-[200px]" id="qrcode-wrapper">
                    <div id="qrcode"></div>
                </div>

                <!-- Info URL téléversée le cas échéant -->
                <p id="active-target-text" class="text-xs text-slate-400 text-center truncate max-w-full mb-4 px-2"></p>

                <!-- Bouton Télécharger -->
                <button id="download-btn" onclick="downloadQR()" disabled class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2.5 px-4 rounded-xl transition flex items-center justify-center space-x-2 text-sm shadow-lg shadow-emerald-600/20">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Télécharger PNG</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 text-xs text-slate-500 border-t border-slate-800 mt-8">
        <p>Générateur de QR Code — Développé avec PHP, JS & Tailwind CSS</p>
    </footer>

    <script>
        lucide.createIcons();

        // Références DOM
        const textInput = document.getElementById('text-input');
        const colorDark = document.getElementById('color-dark');
        const colorLight = document.getElementById('color-light');
        const downloadBtn = document.getElementById('download-btn');
        const qrContainer = document.getElementById('qrcode');
        const activeTargetText = document.getElementById('active-target-text');

        let qrcodeObj = null;
        const uploadedUrl = "<?= $uploadedImageUrl ?>";

        // Initialiser le QR Code
        function generateQR(text) {
            qrContainer.innerHTML = '';
            if (!text || text.trim() === '') {
                downloadBtn.disabled = true;
                activeTargetText.innerText = '';
                return;
            }

            qrcodeObj = new QRCode(qrContainer, {
                text: text,
                width: 200,
                height: 200,
                colorDark: colorDark.value,
                colorLight: colorLight.value,
                correctLevel: QRCode.CorrectLevel.H
            });

            downloadBtn.disabled = false;
            activeTargetText.innerText = text;
        }

        // Écouteurs d'événements
        textInput.addEventListener('input', (e) => generateQR(e.target.value));
        colorDark.addEventListener('input', () => generateQR(textInput.value));
        colorLight.addEventListener('input', () => generateQR(textInput.value));

        // Switch Tabs
        function switchTab(tab) {
            const btnText = document.getElementById('tab-text-btn');
            const btnImage = document.getElementById('tab-image-btn');
            const secText = document.getElementById('section-text');
            const secImage = document.getElementById('section-image');

            if (tab === 'text') {
                btnText.className = "pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-indigo-500 text-indigo-400 transition-colors";
                btnImage.className = "pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition-colors";
                secText.classList.remove('hidden');
                secImage.classList.add('hidden');
                generateQR(textInput.value);
            } else {
                btnImage.className = "pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-indigo-500 text-indigo-400 transition-colors";
                btnText.className = "pb-3 px-2 font-medium text-sm flex items-center space-x-2 border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition-colors";
                secImage.classList.remove('hidden');
                secText.classList.add('hidden');
                if(uploadedUrl) {
                    generateQR(uploadedUrl);
                }
            }
        }

        // Afficher nom du fichier sélectionné
        function showFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                fileNameDisplay.innerText = "Fichier sélectionné : " + input.files[0].name;
                fileNameDisplay.classList.add('text-indigo-400');
            }
        }

        // Télécharger le QR code en PNG
        function downloadQR() {
            const img = qrContainer.querySelector('img');
            const canvas = qrContainer.querySelector('canvas');

            let imageSrc = '';
            if (img && img.src) {
                imageSrc = img.src;
            } else if (canvas) {
                imageSrc = canvas.toDataURL("image/png");
            }

            if (imageSrc) {
                const link = document.createElement('a');
                link.href = imageSrc;
                link.download = 'qrcode.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        // Auto-chargement après upload
        if (uploadedUrl) {
            switchTab('image');
        }
    </script>
</body>
</html>