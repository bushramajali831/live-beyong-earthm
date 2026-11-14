<?php

$url = "https://api.open-meteo.com/v1/forecast?latitude=31.95&longitude=35.91&hourly=temperature_2m,relative_humidity_2m,precipitation_probability,soil_temperature_0_to_7cm&timezone=Asia%2FAmman";

$response = file_get_contents($url);

$data = json_decode($response, true);

$temperature = $data['hourly']['temperature_2m'][0];
$humidity = $data['hourly']['relative_humidity_2m'][0];

echo "Temperature: " . $temperature . " °C";
echo "<br>";
echo "Humidity: " . $humidity . " %";

?>


<?php
// معالجة البيانات المستقبلة عبر PHP عند إرسال النموذج
$aiResponse = "";
$timelineData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $landNum = htmlspecialchars($_POST['landNumber']);
    $soilType = htmlspecialchars($_POST['soilType']);
    $currentCrop = htmlspecialchars($_POST['currentCrop']);
    $farmerQuestion = isset($_POST['farmerQuestion']) ? htmlspecialchars($_POST['farmerQuestion']) : "";

    // منطق توليد خطة زمنية ذكية متعددة السنوات بناءً على نوع التربة والمحصول الحالي
    if ($soilType == 'clay') {
        $timelineData = [
            "السنة الأولى (الحالية)" => "زراعة $currentCrop مع تطبيق ري منتظم واستخدام تحليل رطوبة السطح (SMAP).",
            "السنة الثانية" => "تناوب المحاصيل: زراعة البقوليات (عدس أو حمص) لتثبيت النيتروجين وتحسين تهوية التربة الطينية.",
            "السنة الثالثة" => "العودة للحبوب الاستراتيجية (قمح أو شعير) للاستفادة من الخصوبة المتجددة."
        ];
    } else {
        $timelineData = [
            "السنة الأولى (الحالية)" => "زراعة $currentCrop مع الاعتماد على أصناف مقاومة للجفاف.",
            "السنة الثانية" => "زراعة محاصيل زيتونية أو أعلاف تتناسب مع خصائص التربة الرملية/الكلسية.",
            "السنة الثالثة" => "إراحة التربة (Bare Fallow) مع دورة تسميد عضوي لرفع كفاءة الاحتفاظ بالمياه."
        ];
    }

    // محاكاة نموذج الذكاء الاصطناعي (AI Model) للرد على استفسارات المزارع
    if (!empty($farmerQuestion)) {
        $qLower = strtolower($farmerQuestion);
        if (strpos($qLower, 'ري') !== false || strpos($qLower, 'ماء') !== false) {
            $aiResponse = "🤖 [مدرب ناسا للذكاء الاصطناعي]: بناءً على إحداثيات أرضك رقم $landNum ومؤشرات رطوبة السطح (SMAP)، أنصحك بتقليل الري بنسبة 15% خلال الأسابيع القادمة لتجنب تعفن الجذور في التربة الـ $soilType.";
        } else if (strpos($qLower, 'سماد') !== false || strpos($qLower, 'تسميد') !== false) {
            $aiResponse = "🤖 [مدرب ناسا للذكاء الاصطناعي]: نظراً لأن محصولك السابق تطلب تغذية عالية، يوصي النموذج بوضع سماد نتروجيني متوازن في بداية فصل الربيع القادم.";
        } else {
            $aiResponse = "🤖 [مدرب ناسا للذكاء الاصطناعي]: استناداً إلى بيانات أرضك ورصد الأقمار الصناعية (Sentinel-2)، خطتك الزمنية تسير بشكل ممتاز! هل لديك استفسار محدد حول الري أو التسميد؟";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TerraVision - المخطط الزمني الذكي ومساعد ناسا الذكي</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #121212; color: #fff; font-family: Tahoma, sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: rgba(255,255,255,0.05); padding: 30px; border-radius: 15px; border: 1px solid #2e7d32; }
        .timeline-item { background: rgba(46, 125, 50, 0.2); border-right: 4px solid #81c784; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        .ai-box { background: rgba(255, 152, 0, 0.15); border: 1.5px solid #ffb74d; padding: 20px; border-radius: 12px; margin-top: 25px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: #fff; border-radius: 6px; }
        button { background: #2e7d32; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background: #388e3c; }
    </style>
</head>
<body>

<div class="container">
    <h1>🌱 TerraVision - التخطيط الذكي للسنوات والدعم بالذكاء الاصطناعي</h1>
    <p>أدخل بيانات الأرض لتوليد المخطط الزمني المتجدد وتفعيل مساعد ناسا الذكي.</p>

    <!-- نموذج الإدخال -->
    <form method="POST" action="">
        <label>رقم الأرض:</label>
        <input type="text" name="landNumber" value="<?php echo isset($_POST['landNumber']) ? $_POST['landNumber'] : ''; ?>" required>

        <label>نوع التربة:</label>
        <select name="soilType" required>
            <option value="clay" <?php echo (isset($_POST['soilType']) && $_POST['soilType']=='clay')?'selected':''; ?>>طينية (Clay)</option>
            <option value="sandy" <?php echo (isset($_POST['soilType']) && $_POST['soilType']=='sandy')?'selected':''; ?>>رملية أو كلسية</option>
        </select>

        <label>المحصول المزروع حالياً:</label>
        <input type="text" name="currentCrop" value="<?php echo isset($_POST['currentCrop']) ? $_POST['currentCrop'] : 'قمح'; ?>" required>

        <label>اسأل مساعد ناسا الذكي (AI Model):</label>
        <textarea name="farmerQuestion" placeholder="مثلاً: متى أفضل وقت لري هذه الأرض؟ أو ما نوع السماد المناسب؟"><?php echo isset($_POST['farmerQuestion']) ? $_POST['farmerQuestion'] : ''; ?></textarea>

        <button type="submit">توليد المخطط الزمني وتحليل الذكاء الاصطناعي</button>
    </form>

    <?php if (!empty($timelineData)): ?>
        <hr style="margin: 30px 0; border-color: #444;">
        <h2>📅 المخطط الزمني للدورة الزراعية (متعدد السنوات):</h2>
        <?php foreach ($timelineData as $year => $plan): ?>
            <div class="timeline-item">
                <strong style="color: #81c784; font-size: 16px;"><?php echo $year; ?></strong>
                <p style="margin: 5px 0 0 0;"><?php echo $plan; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($aiResponse)): ?>
        <div class="ai-box">
            <h3>💬 إجابة نموذج الذكاء الاصطناعي للمزارع:</h3>
            <p style="font-size: 15px; line-height: 1.6; margin: 0;"><?php echo $aiResponse; ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>