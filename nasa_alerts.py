import datetime
import smtplib
from email.header import Header
from email.mime.text import MIMEText
import requests

# إعدادات بريد الإرسال (مثال: بريد الموقع أو Gmail)
SMTP_SERVER = "smtp.gmail.com"
SMTP_PORT = 587
SENDER_EMAIL = "your-farm-email@gmail.com"
SENDER_PASSWORD = "your-app-password"

# إحداثيات أرض المزارع (خط الطول وخط العرض)
LATITUDE = 31.9539  # مثال: عمان
LONGITUDE = 35.9106


def fetch_nasa_weather_data():
  # تحديد التاريخ الحالي وتاريخ التوقعات القادمة (يومين مقبلين)
  today = datetime.date.today()
  start_date = (today - datetime.timedelta(days=5)).strftime("%Y%m%d")
  end_date = today.strftime("%Y%m%d")

  # رابط NASA POWER API لجلب درجات الحرارة الصغرى وسرعة الرياح
  url = f"https://power.larc.nasa.gov/api/temporal/daily/point?parameters=T2M_MIN,WS2M&community=AG&longitude={LONGITUDE}&latitude={LATITUDE}&start={start_date}&end={end_date}&format=JSON"

  try:
    response = requests.get(url)
    data = response.json()
    return data["properties"]["parameter"]
  except Exception as e:
    print("خطأ في الاتصال ببيانات ناسا:", e)
    return None


def check_disasters_and_alert(farmer_email, farmer_name):
  weather_data = fetch_nasa_weather_data()
  if not weather_data:
    return

  t2m_min_dict = weather_data.get("T2M_MIN", {})
  ws2m_dict = weather_data.get("WS2M", {})

  # الحصول على آخر القيم المسجلة
  dates = sorted(t2m_min_dict.keys())
  if not dates:
    return

  latest_date = dates[-1]
  min_temp = t2m_min_dict[latest_date]
  wind_speed = ws2m_dict.get(latest_date, 0)

  # فحص الشروط التحذيرية (الكوارث الطبيعية)
  alerts = []

  # 1. فحص خطر الانجماد (درجة الحرارة أقل من أو تساوي صفر)
  if min_temp <= 0:
    alerts.append(
        f"⚠️ تحذير انجماد وصقيع! درجات الحرارة ستنخفض إلى {min_temp}°C."
    )

  # 2. فحص خطر العواصف والرياح القوية (سرعة الرياح أكبر من 15 متر/ثانية)
  if wind_speed > 15:
    alerts.append(
        f"🌪️ تحذير عواصف! سرعة الرياح المتوقعة عالية جداً وتصل إلى"
        f" {wind_speed} م/ث."
    )

  # إذا وُجد تحذير، يتم إرسال الإيميل للمزارع
  if alerts:
    send_email_alert(farmer_email, farmer_name, alerts)


def send_email_alert(to_email, farmer_name, alerts_list):
  subject = "🚨 تحذير عاجل: خطر مناخي يهدد مزرعتك خلال 48 ساعة!"
  body = f"مرحباً المزارع الكريم {farmer_name}،\n\n"
  body += (
      "بناءً على تحليلات بيانات الأقمار الصناعية (NASA POWER)، تم رصد المؤشرات"
      " التالية في منطقتك:\n\n"
  )

  for alert in alerts_list:
    body += f"- {alert}\n"

  body += (
      "\nيرجى اتخاذ الاحتياطات اللازمة لحماية المحاصيل في أقرب وقت.\nفريق"
      " إدارة المزرعة الذكية."
  )

  msg = MIMEText(body, "plain", "utf-8")
  msg["Subject"] = Header(subject, "utf-8")
  msg["From"] = SENDER_EMAIL
  msg["To"] = to_email

  try:
    server = smtplib.SMTP(SMTP_SERVER, SMTP_PORT)
    server.starttls()
    server.login(SENDER_EMAIL, SENDER_PASSWORD)
    server.sendmail(SENDER_EMAIL, to_email, msg.as_string())
    server.quit()
    print(f"تم إرسال التحذير بنجاح إلى الإيميل: {to_email}")
  except Exception as e:
    print("فشل في إرسال البريد الإلكتروني:", e)


# مثال على التشغيل لمزارع مسجل في قاعدة البيانات
# check_disasters_and_alert("farmer@example.com", "أحمد")