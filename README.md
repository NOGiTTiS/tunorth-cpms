# TUNorth CPMS (Cooperative Project Management System)

ระบบจัดการโครงงานสหกิจศึกษา (หรือโปรเจกต์อื่นๆ) พัฒนาด้วยสถาปัตยกรรม **PHP MVC (Custom)** พร้อมรองรับการจำลองสภาพแวดล้อม (Environment) ในการพัฒนาด้วย **Docker**

## 📂 โครงสร้างโปรเจกต์ (Project Structure)

โปรเจกต์นี้ถูกออกแบบมาในรูปแบบ MVC (Model-View-Controller) เพื่อให้ง่ายต่อการดูแลรักษา:

- `app/` - เก็บไฟล์หลักของแอปพลิเคชัน
  - `controllers/` - ตัวควบคุมการทำงานของแอปพลิเคชัน
  - `core/` - ไฟล์หลักที่ควบคุมการทำงานของ System (เช่น `App.php`, `Controller.php`, `Database.php`)
  - `models/` - ไฟล์สำหรับจัดการและเชื่อมต่อกับฐานข้อมูล
  - `views/` - ไฟล์ส่วนหน้าบ้าน (UI) ที่แสดงผลให้ผู้ใช้เห็น
- `config/` - ไฟล์สำหรับตั้งค่าต่างๆ เช่น การเชื่อมต่อฐานข้อมูล
- `css/` / `js/` - ไฟล์ส่วนของการแสดงผลและการแจ้งเตือนต่างๆ ทางฝั่งไคลเอนต์ (Frontend Assets)
- `uploads/` - ไดเรกทอรีสำหรับเก็บไฟล์ที่ผู้ใช้อัปโหลดเข้าสู่ระบบ
- `index.php` - Entry Point หลักของโปรเจกต์

## 🚀 สิ่งที่ต้องมี (Prerequisites)

ก่อนที่จะเริ่มรันโปรเจกต์ โปรดตรวจสอบให้แน่ใจว่าได้ติดตั้งซอฟต์แวร์เหล่านี้เรียบร้อยแล้ว:
- [Docker](https://www.docker.com/products/docker-desktop) และ [Docker Compose](https://docs.docker.com/compose/install/)

## 🛠 ขั้นตอนการจำลองเซิร์ฟเวอร์ด้วย Docker (Installation & Setup)

โปรเจกต์นี้ใช้ Docker ประกอบไปด้วย 3 Service หลักได้แก่: `php:8.2-apache`, `mysql:8.0`, และ `phpmyadmin`

1. **Clone Repository (ถ้ายังไม่ได้ทำ)**
   ```bash
   git clone https://github.com/NOGiTTiS/tunorth-cpms.git
   cd tunorth-cpms
   ```

2. **สั่งรันคอนเทนเนอร์ (Start Services)**
   ```bash
   docker-compose up -d
   ```
   *คำสั่งนี้จะทำการดาวน์โหลด Image และสร้างคอนเทนเนอร์ขึ้นมาทำงานใน Background*

3. **การเข้าใช้งาน (Access the Application)**
   - เว็บไซต์ (แอปพลิเคชัน): [http://localhost:8080](http://localhost:8080)
   - จัดการฐานข้อมูล (phpMyAdmin): [http://localhost:8081](http://localhost:8081)

## 🗄 ข้อมูลสำหรับการเชื่อมต่อฐานข้อมูล (Database Configuration)

ตั้งค่าเริ่มต้นผ่านไฟล์ `docker-compose.yml`:
- **Host**: `db`
- **Port**: `3306`
- **Database Name**: `cpms_db`
- **Username**: `root`
- **Password**: `root_password`

## ⚙️ การอัปเดตและเริ่มต้นฐานข้อมูล
ในโปรเจกต์นี้มีไฟล์สคริปต์สำหรับอัปเดตหรือเพิ่มโครงสร้าง/ข้อมูลบางส่วนในฐานข้อมูล สามารถสั่งรันไฟล์เหล่านี้ผ่านเบราว์เซอร์ได้หากจำเป็น:
- `/update_db_criteria.php`
- `/update_db_grading.php`
- `/update_db_steps_files.php`

## 🛑 การหยุดแอปพลิเคชัน (Stop Services)

หากต้องการหยุดแอปพลิเคชัน ให้ใช้คำสั่ง:
```bash
docker-compose down
```
*(เพิ่ม flag `-v` หากต้องการลบข้อมูลใน Volume ด้วย: `docker-compose down -v`)*

## 📝 หมายเหตุเพิ่มเติม (Notes)
- หากเกิดปัญหาเรื่อง Path ของไฟล์รูปภาพหรือไฟล์เส้นทางต่างๆ สามารถปรับแก้ `BASE_URL` ได้ที่บรรทัดประมาณที่ 12 ในไฟล์ `index.php` (ในกรณีที่รันด้วยเซิร์ฟเวอร์ภายนอกที่ไม่ได้อยู่ใน root path ตัวอย่างเช่น `/tunorth-cpms`)
