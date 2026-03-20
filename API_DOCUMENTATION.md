# Event Management System API Documentation

## Overview
This document provides a complete reference for the Event Management System API.

- **Base URL:** `http://localhost:8000/api/v1`
- **Auth:** Uses Laravel Sanctum token authentication. Pass the access token via `Authorization: Bearer {token}`.

---

## 1. Authentication

### 1.1 Register
- **Method:** POST
- **Endpoint:** `/auth/register`
- **Auth:** None

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "1234567890",
  "role": "customer"
}
```

### 1.2 Login
- **Method:** POST
- **Endpoint:** `/auth/login`
- **Auth:** None

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### 1.3 Refresh Token
- **Method:** POST
- **Endpoint:** `/auth/refresh-token`
- **Auth:** Uses refresh token cookie set by login response

### 1.4 Logout
- **Method:** POST
- **Endpoint:** `/auth/logout`
- **Auth:** Bearer Token

### 1.5 Forgot Password
- **Method:** POST
- **Endpoint:** `/auth/forgot-password`
- **Auth:** None

**Body:**
```json
{ "email": "john@example.com" }
```

### 1.6 Reset Password
- **Method:** POST
- **Endpoint:** `/auth/reset-password`
- **Auth:** None

**Body:**
```json
{
  "email": "john@example.com",
  "token": "<reset_token>",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## 2. Events

### 2.1 List Events
- **Method:** GET
- **Endpoint:** `/events`
- **Auth:** None

**Query (optional):** `search`, `date`, `location`

### 2.2 Get Event
- **Method:** GET
- **Endpoint:** `/events/{id}`
- **Auth:** None

### 2.3 Create Event
- **Method:** POST
- **Endpoint:** `/events`
- **Auth:** Bearer (organizer or admin)

**Body:**
```json
{
  "title": "My Event",
  "description": "Event description",
  "date": "2026-07-15 18:00:00",
  "location": "Venue"
}
```

### 2.4 Update Event
- **Method:** PATCH
- **Endpoint:** `/events/{id}`
- **Auth:** Bearer (organizer or admin)

**Body:** (partial update allowed)
```json
{ "title": "Updated Title" }
```

### 2.5 Delete Event
- **Method:** DELETE
- **Endpoint:** `/events/{id}`
- **Auth:** Bearer (organizer or admin)

---

## 3. Tickets

### 3.1 List Tickets for Event
- **Method:** GET
- **Endpoint:** `/events/{event_id}/tickets`
- **Auth:** None

### 3.2 Create Ticket
- **Method:** POST
- **Endpoint:** `/events/{event_id}/tickets`
- **Auth:** Bearer (organizer or admin)

**Body:**
```json
{
  "type": "VIP",
  "price": 100.00,
  "quantity": 50
}
```

### 3.3 Update Ticket
- **Method:** PUT
- **Endpoint:** `/tickets/{id}`
- **Auth:** Bearer (organizer or admin)

**Body:** (partial update allowed)
```json
{ "price": 120.00 }
```

### 3.4 Delete Ticket
- **Method:** DELETE
- **Endpoint:** `/tickets/{id}`
- **Auth:** Bearer (organizer or admin)

---

## 4. Bookings (Customer)

### 4.1 Create Booking
- **Method:** POST
- **Endpoint:** `/tickets/{ticket}/bookings`
- **Auth:** Bearer (customer)

**Body:**
```json
{ "quantity": 2 }
```

### 4.2 List My Bookings
- **Method:** GET
- **Endpoint:** `/bookings`
- **Auth:** Bearer (customer)

**Query (optional):** `status`, `start_date`, `end_date`

### 4.3 Get Booking
- **Method:** GET
- **Endpoint:** `/bookings/{id}`
- **Auth:** Bearer (customer)

### 4.4 Cancel Booking
- **Method:** PUT
- **Endpoint:** `/bookings/{id}/cancel`
- **Auth:** Bearer (customer)

---

## 5. Bookings (Admin)

### 5.1 List All Bookings
- **Method:** GET
- **Endpoint:** `/admin/bookings`
- **Auth:** Bearer (admin)

### 5.2 Get Booking
- **Method:** GET
- **Endpoint:** `/admin/bookings/{id}`
- **Auth:** Bearer (admin)

### 5.3 Cancel Booking
- **Method:** PUT
- **Endpoint:** `/admin/bookings/{id}/cancel`
- **Auth:** Bearer (admin)

---

## 6. Payments

### 6.1 Process Payment
- **Method:** POST
- **Endpoint:** `/bookings/{bookingId}/payment`
- **Auth:** Bearer (customer)

### 6.2 Get Payment
- **Method:** GET
- **Endpoint:** `/payments/{id}`
- **Auth:** Bearer (customer)

---

## Notes
- **Soft deletes** are enabled for most models (events, tickets, bookings, users).
- **Booking concurrency** is guarded using `lockForUpdate()` to avoid overbooking.
- **Payment processing is simulated** (random success/failure).
