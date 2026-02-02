# Event Management System

## Website Link 
https://student.heraldcollege.edu.np/~np03cs4a240370/Event_Management_System/public/index.php

## Overview
A web-based application for managing events, allowing organizers to create and manage events, and attendees to search and register for them. Built with PHP and MySQL.

##  Setup Instructions

### Prerequisites
- **XAMPP** (or any PHP/MySQL Development Environment)
- **Web Browser**

### Installation Steps
1.  **Clone/Download** the project to your web server directory (e.g., `htdocs` in XAMPP).
    ```path
    /Applications/XAMPP/xamppfiles/htdocs/Event_Management_System
    ```
2.  **Database Setup**:
    - Open **phpMyAdmin**.
    - Create a new database named `np03cs4a240370` (or update `config/db.php` with your database name).
    - Import the provided SQL structure/data (if available) or ensure the following tables exist: `users`, `events`, `categories`, `registrations`.
3.  **Launch**:
    - Open your browser and navigate to:
      `http://localhost/Event_Management_System/public/index.php`

##  Login Credentials

> **Note**: Only one admin is authorized for CRUD Operations So use below oragnizer account to perform CRUD Operations.

**Organizer Account** (Example/Placeholder)
- **Email**: `aryalanjul123@gmail.com`
- **Password**: `Anjul@333`
- *Role*: Can create, edit, and delete events.

**Attendee Account** (Example/Placeholder)
- **Email**: `user@example.com`
- **Password**: `password123`
- *Role*: Can search and book events.

## ✨ Features Implemented

### User Management
- **Authentication**: Secure Login and Registration system.
- **Role-Based Access**: Separation of Organizer and Attendee capabilities.

### Event Management (Organizer)
- **Create Events**: Add new events with details like title, description, date, location, capacity, and cover image.
- **Edit/Delete**: distinct administrative options for event owners.
- **Manage Attendees**: View list of registered users for specific events.

### Discovery & Booking (Attendee)
- **Live Search**: Real-time event search by title, location, or category.
- **Visual Results**: specific search results featuring event images.
- **Event Booking**: Simple registration flow with capacity tracking (Sold Out status).
- **Responsive Design**: Professional, mobile-friendly interface with modern styling.

### Technical & Security
- **Security**: CSRF protection, input sanitization, and prepared statements validation.
- **Architecture**: Organized functionality with separation of concerns (`public`, `includes`, `config`, `assets`).
- **Styling**: Modern CSS using 'Inter' typography and glass-like card designs.

##  Known Issues
- **Image Uploads**: Ensure the `assets/uploads/` directory has write permissions (`chmod 777` on Unix-based systems) for image uploads to work correctly.
- **Email**: Password reset functionality is currently a placeholder (if applicable).
