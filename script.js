let defaultLat = 31.9539;
let defaultLng = 35.9106;

const map = L.map('satelliteMap').setView([defaultLat, defaultLng], 14);

L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; NASA / TerraVision'
}).addTo(map);

let landMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

function updateCoordinates(lat, lng) {
    const formattedCoords = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('location').value = formattedCoords;
    document.getElementById('coordsDisplay').innerText = `GPS: ${formattedCoords}`;
}

map.on('click', function(e) {
    const { lat, lng } = e.latlng;
    landMarker.setLatLng([lat, lng]);
    updateCoordinates(lat, lng);
});

landMarker.on('dragend', function(e) {
    const { lat, lng } = landMarker.getLatLng();
    updateCoordinates(lat, lng);
});

function locateUser() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 17);
            landMarker.setLatLng([lat, lng]);
            updateCoordinates(lat, lng);
        }, function() {
            alert(currentLang === 'ar' ? 'يرجى تفعيل صلاحية الموقع (GPS).' : 'Please allow GPS location access.');
        });
    } else {
        alert("GPS is not supported.");
    }
}

function handleCropChange(type) {
    let selectVal = document.getElementById(type === 'current' ? 'currentCrop' : (type === 'prev' ? 'previousCrop' : 'futureCrop')).value;
    let customGroup = document.getElementById(type === 'current' ? 'customCurrentCropGroup' : (type === 'prev' ? 'customPrevCropGroup' : 'customFutureCropGroup'));
    
    if (selectVal === 'custom') {
        customGroup.style.display = 'block';
    } else {
        customGroup.style.display = 'none';
    }
}

let savedLandsList = [];

function collectCurrentLandData() {
    const coords = landMarker.getLatLng();
    const goalChecked = document.querySelector('input[name="farmingGoal"]:checked');

    let currVal = document.getElementById('currentCrop').value;
    if (currVal === 'custom') currVal = document.getElementById('customCurrentCrop').value;

    let prevVal = document.getElementById('previousCrop').value;
    if (prevVal === 'custom') prevVal = document.getElementById('customPrevCrop').value;

    let futVal = document.getElementById('futureCrop').value;
    if (futVal === 'custom') futVal = document.getElementById('customFutureCrop').value;

    return {
        landNumber: document.getElementById('landNumber').value,
        landArea: document.getElementById('landArea').value,
        areaUnit: document.getElementById('areaUnit').value,
        locationText: document.getElementById('location').value,
        latitude: coords.lat,
        longitude: coords.lng,
        soilType: document.getElementById('soilType').value,
        currentCrop: currVal,
        previousCrop: prevVal,
        futureCrop: futVal,
        farmingGoal: goalChecked ? goalChecked.value : ''
    };
}

// 1. زر حفظ معلومات الأرض
document.getElementById('landForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const landData = collectCurrentLandData();
    savedLandsList.push(landData);
    localStorage.setItem('terraVision_allLands', JSON.stringify(savedLandsList));

    alert(currentLang === 'ar' ? 'تم حفظ معلومات الأرض بنجاح! جاري الانتقال للفورم التالي...' : 'Land information saved! Redirecting...');

    window.location.href = 'form4.html';
});

// 2. زر إضافة أرض جديدة
function addAnotherLand() {
    const landNum = document.getElementById('landNumber').value.trim();
    if (!landNum) {
        alert(currentLang === 'ar' ? 'يرجى إدخال رقم الأرض الحالي أولاً قبل إضافة أرض جديدة.' : 'Please enter the current land number first.');
        return;
    }

    const landData = collectCurrentLandData();
    savedLandsList.push(landData);
    localStorage.setItem('terraVision_allLands', JSON.stringify(savedLandsList));

    document.getElementById('landForm').reset();
    
    document.getElementById('customCurrentCropGroup').style.display = 'none';
    document.getElementById('customPrevCropGroup').style.display = 'none';
    document.getElementById('customFutureCropGroup').style.display = 'none';

    document.querySelector('.form-panel').scrollIntoView({ behavior: 'smooth' });

    alert(currentLang === 'ar' 
        ? `✅ تم حفظ الأرض رقم (${landData.landNumber}) بنجاح! يمكنك الآن إدخال بيانات الأرض الجديدة.` 
        : `✅ Saved land number (${landData.landNumber}) successfully! You can now enter the new land details.`
    );
}