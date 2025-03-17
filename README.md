# PetStore API - Laravel Application

This is a Laravel-based application that interacts with the [Swagger PetStore API](https://petstore.swagger.io/). It allows users to perform CRUD (Create, Read, Update, Delete) operations on pets using a simple interface.

## 📌 Features

-   Add new pets
-   Retrieve a list of pets
-   Edit pet details
-   Delete pets
-   Flash messages for success and error notifications
-   Simple UI with pagination and search functionality

---

## 🚀 Installation Guide

### 1️⃣ **Clone the Repository**

```bash
git clone https://github.com/your-repo/petstore-laravel.git
cd petstore-laravel
```

### 2️⃣ **Install Laravel Dependencies**

Make sure you have [Composer](https://getcomposer.org/) installed.

```bash
composer install
```

### 3️⃣ **Set Up Environment File**

Duplicate the `.env.example` file and rename it to `.env`:

```bash
cp .env.example .env
```

Then, update the necessary environment variables in `.env`:

```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=http://localhost
```

### 4️⃣ **Generate the Application Key**

```bash
php artisan key:generate
```

### 5️⃣ **Start the Laravel Development Server**

```bash
php artisan serve
```

Your application should now be running at `http://127.0.0.1:8000/`.

---

## 📚 API Integration

This application communicates with the **Swagger PetStore API** to manage pets. The main API endpoints used are:

-   `POST /pet` - Add a new pet
-   `GET /pet/findByStatus?status=available` - Get a list of available pets
-   `GET /pet/{id}` - Get a pet by ID
-   `PUT /pet` - Update pet information
-   `DELETE /pet/{id}` - Delete a pet

### **Configuration in Laravel**

-   API requests are handled using the **GuzzleHTTP client**.
-   The service layer (`PetService.php`) manages API communication.
-   Error handling and logging are implemented for debugging.

---

## 🎨 User Interface

### **Features:**

-   Simple UI for managing pets
-   Flash messages for success and error notifications
-   Pagination support
-   Search functionality
-   "Show My Pets" vs "Show All Pets" filtering

---

## 🔄 CRUD Operations

### **Adding a Pet**

Navigate to `/pets/create`, fill out the form, and submit.

### **Listing Pets**

The `/pets` page displays a list of pets with pagination.

### **Editing a Pet**

Click the "Edit" button next to a pet to modify its details.

### **Deleting a Pet**

Click the "Delete" button next to a pet to remove it.

---

## ⚠️ Error Handling

-   If an API request fails, a flash message will be displayed.
-   Laravel's logging system (`storage/logs/laravel.log`) records errors for debugging.

---

## 🔧 Troubleshooting

### **Common Issues & Fixes**

#### **1. "Could not find driver" error**

Make sure SQLite or MySQL is installed and enabled in `php.ini`.

#### **2. "API returns 404 for existing pets"**

-   The PetStore API may clear its database periodically.
-   Consider persisting data locally.

#### **3. "Session data lost after refresh"**

-   Ensure Laravel's session driver is set correctly in `.env`:
    ```
    SESSION_DRIVER=file
    LOG_CHANNEL=daily
    LOG_MAX_FILES=30
    LOG_LEVEL=debug
    LOG_FORMAT=line
    LOG_RESPONSE=true
    LOG_REQUEST=true
    LOG_QUERY=true
    LOG_QUERY_THRESHOLD=0
    LOG_STACK=single
    LOG_DEPRECATIONS_CHANNEL=null
    ```

---

## 🏗️ Future Enhancements

-   Implement authentication
-   Store pet data locally instead of relying on the external API
-   Improve UI styling

---

## 📜 License

This project is open-source and available under the MIT License.

---

## 👨‍💻 Author

Developed by **Robert Luczynski**. Feel free to reach out with any questions or contributions!

---
