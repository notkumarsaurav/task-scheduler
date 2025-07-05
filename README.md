# ✅ PHP Task Scheduler with Email Reminders

> **Author:** Kumar Saurav  
> **Tech Stack:** PHP 8.2, PHPMailer, JSON File Storage  
> **Description:** A simple task planner that allows users to add tasks, subscribe via email with OTP verification, and receive automated task reminders every hour.

---

## 📌 Features

- **Task Management**
  - Add, complete/incomplete, and delete tasks.
  - Prevents duplicate task entries.
  - Tasks stored in `tasks.txt` in JSON format.

- **Email Subscription**
  - Users can subscribe with their email.
  - 6-digit OTP verification via email (PHPMailer + Gmail SMTP).
  - Only verified users receive reminders.

- **Automated Reminders**
  - Every hour, a script sends pending task reminders to verified users.
  - Implemented using `cron.php` (Linux CRON) or `run_cron.bat` (Windows Task Scheduler).

- **Unsubscribe System**
  - Every reminder email includes a 1-click unsubscribe link.
  - On click, the user is removed from the subscriber list.

---