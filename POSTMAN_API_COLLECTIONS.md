# Event Booking System - Postman API Collections

## Overview
This document contains all API endpoints for the Event Booking System, organized by feature. All endpoints use the `/api/v1` prefix and follow RESTful conventions.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
Most endpoints require authentication. Include the access token in the Authorization header:
```
Authorization: Bearer {access_token}
```

---

## 1. Authentication Endpoints

### 1.1 Register User
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/register`  
**Auth:** None required

**Request Body:**
```json
"name":"Abhishek Kumar",
"email":"visionabhi0503@gmail.com",
"password":"password123",
"phone": "1234567890",
"role": "customer",
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "name": "Abhishek Kumar",
            "email": "visionabhi0503@gmail.com",
            "phone": "1234567890",
            "role": "customer",
            "updated_at": "2026-03-18T12:14:47.000000Z",
            "created_at": "2026-03-18T12:14:47.000000Z",
            "id": 53
        },
        "access_token": "16|wyGrWZRe2CBEX4ZIzwo52dgEyvDXjl30EhQNuZRsacc8e694"
    }
}
```

### 1.2 Login User
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/login`  
**Auth:** None required

**Request Body:**
```json
{
    "email":"visionabhi0503@gmail.com",
    "password":"password123",
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 53,
            "name": "Abhishek Kumar",
            "email": "visionabhi0503@gmail.com",
            "email_verified_at": null,
            "phone": "1234567890",
            "role": "customer",
            "created_at": "2026-03-18T12:14:47.000000Z",
            "updated_at": "2026-03-18T12:14:47.000000Z",
            "deleted_at": null
        },
        "access_token": "18|lg4BlCWunwcpZseqcXmS9T1rkYz49y9p2xovvTWv447db45c"
    }
}
```

### 1.3 Logout User
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/logout`  
**Auth:** 18|lg4BlCWunwcpZseqcXmS9T1rkYz49y9p2xovvTWv447db45c

**Request Body:** None

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully",
    "data": null
}
```

### 1.4 Get Current User
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/auth/me`  
**Auth:** 18|lg4BlCWunwcpZseqcXmS9T1rkYz49y9p2xovvTWv447db45c

**Success Response (200):**
```json
{
    "success": true,
    "message": "User data retrieved",
    "data": {
        "id": 53,
        "name": "Abhishek Kumar",
        "email": "visionabhi0503@gmail.com",
        "email_verified_at": null,
        "phone": "1234567890",
        "role": "customer",
        "created_at": "2026-03-18T12:14:47.000000Z",
        "updated_at": "2026-03-18T12:14:47.000000Z",
        "deleted_at": null
    }
}
```

### 1.5 Refresh Token
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/refresh-token`  
**Auth:** Bearer Token required (the refresh token is set in a cookie)

**Request Body:** None

**Success Response (200):**
```json
{
    "success": true,
    "message": "Token refreshed",
    "data": {
        "access_token": "22|aaOHW2CJA6Lj3NmDMw2CsvBzjp4rhH284YKQiIok2e6e26e5"
    }
}
```

### 1.6 Forgot Password
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/forgot-password`  
**Auth:** None required

**Request Body:**
```json
{
  "email": "visionabhi0503@gmail.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "If a matching account was found, a password reset email has been sent.",
    "data": null
}
```

### 1.7 Reset Password
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/auth/reset-password`  
**Auth:** None required

**Request Body:**
```json
{
    "email":"visionabhi0503@gmail.com",
    "token":"<reset-token>",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password has been reset successfully",
    "data": null
}
```

---

## 2. Event Management Endpoints

### 2.1 Create Event
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/events`  
**Auth:** Bearer Token required (organizer or admin)

**Request Body:**
```json
{
  "title": "Summer Music Festival",
  "description": "A great outdoor music festival",
  "date": "2026-07-15 18:00:00",
  "location": "Central Park, New York"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Event created successfully",
    "data": {
        "title": "Summer Music Festival",
        "description": "A great outdoor music festival",
        "date": "2026-07-15T18:00:00.000000Z",
        "location": "Central Park, New York",
        "created_by": 2,
        "updated_at": "2026-03-18T13:26:05.000000Z",
        "created_at": "2026-03-18T13:26:05.000000Z",
        "id": 1
    }
}
```

### 2.2 Get All Events
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/events`  
**Auth:** None required

**Query params (optional):**
- `search` (title)
- `date` (YYYY-MM-DD)
- `location` (substring match)

### 2.3 Get Single Event
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** None required

### 2.4 Update Event
**Method:** PATCH  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** Bearer Token required (organizer or admin)

**Body (any subset):**
```json
{
  "title": "Updated Summer Music Festival",
  "location": "Updated Location"
}
```

### 2.5 Delete Event
**Method:** DELETE  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** Bearer Token required (organizer or admin)

---

## 3. Ticket Endpoints

### 3.1 List Tickets for Event
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/events/{event_id}/tickets`  
**Auth:** None required

### 3.2 Create Ticket
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/events/{event_id}/tickets`  
**Auth:** Bearer Token required (organizer or admin)

**Request Body:**
```json
{
  "type": "VIP",
  "price": 100.00,
  "quantity": 50
}
```

### 3.3 Update Ticket
**Method:** PUT  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{id}`  
**Auth:** Bearer Token required (organizer or admin)

**Body (any subset):**
```json
{
  "price": 120.00
}
```

### 3.4 Delete Ticket
**Method:** DELETE  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{id}`  
**Auth:** Bearer Token required (organizer or admin)

---

## 4. Booking Endpoints (Customer)

### 4.1 Create Booking
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{ticket}/bookings`  
**Auth:** Bearer Token required (customer)

**Request Body:**
```json
{
  "quantity": 2
}
```

### 4.2 List Bookings
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/bookings`  
**Auth:** Bearer Token required (customer)

**Query params (optional):**
- `status`: pending/confirmed/cancelled
- `start_date` and `end_date`: filter by date range

### 4.3 Get Single Booking
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{id}`  
**Auth:** Bearer Token required (customer)

### 4.4 Cancel Booking
**Method:** PUT  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{id}/cancel`  
**Auth:** Bearer Token required (customer)

---

## 5. Booking Endpoints (Admin)

### 5.1 List All Bookings
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/admin/bookings`  
**Auth:** Bearer Token required (admin)

### 5.2 Get Booking by ID
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/admin/bookings/{id}`  
**Auth:** Bearer Token required (admin)

### 5.3 Cancel Booking
**Method:** PUT  
**Endpoint:** `http://localhost:8000/api/v1/admin/bookings/{id}/cancel`  
**Auth:** Bearer Token required (admin)

---

## 6. Payment Endpoints

### 6.1 Process Payment
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{bookingId}/payment`  
**Auth:** Bearer Token required (customer)

### 6.2 Get Payment
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/payments/{id}`  
**Auth:** Bearer Token required (customer)

        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Summer Music Festival",
                "description": "A great outdoor music festival",
                "date": "2026-07-15T18:00:00.000000Z",
                "location": "Central Park, New York",
                "created_by": 2,
                "created_at": "2026-03-18T13:26:05.000000Z",
                "updated_at": "2026-03-18T13:26:05.000000Z",
                "deleted_at": null,
                "creator": {
                    "id": 2,
                    "name": "Abhishek Kumar",
                    "email": "abhi.devcode6@gmail.com",
                    "email_verified_at": null,
                    "phone": "1234567890",
                    "role": "admin",
                    "created_at": "2026-03-18T13:25:40.000000Z",
                    "updated_at": "2026-03-18T13:25:40.000000Z",
                    "deleted_at": null
                }
            }
        ],
        "first_page_url": "http://localhost:8000/api/v1/events?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/api/v1/events?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/events?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "page": null,
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://localhost:8000/api/v1/events",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

### 2.3 Get Single Event
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** Bearer Token required

**Success Response (200):**
```json
{
    "success": true,
    "message": "Event retrieved successfully",
    "data": {
        "id": 1,
        "title": "Summer Music Festival",
        "description": "A great outdoor music festival",
        "date": "2026-07-15T18:00:00.000000Z",
        "location": "Central Park, New York",
        "created_by": 2,
        "created_at": "2026-03-18T13:26:05.000000Z",
        "updated_at": "2026-03-18T13:26:05.000000Z",
        "deleted_at": null,
        "tickets": []
    }
}
```

### 2.4 Update Event
**Method:** PUT/PATCH  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** Bearer Token required (organizer who created the event or admin)

**Request Body:**
```json
{
  "title": "Updated Summer Music Festival",
  "description": "An amazing outdoor music festival",
  "location": "Updated Location"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Event updated successfully",
    "data": {
        "id": 1,
        "title": "Summer Music Festival",
        "description": "A great outdoor music festival",
        "date": "2026-07-15T18:00:00.000000Z",
        "location": "Central Park, New York",
        "created_by": 2,
        "created_at": "2026-03-18T13:26:05.000000Z",
        "updated_at": "2026-03-18T13:26:05.000000Z",
        "deleted_at": null
    }
}
```

### 2.5 Delete Event
**Method:** DELETE  
**Endpoint:** `http://localhost:8000/api/v1/events/{id}`  
**Auth:** Bearer Token required (organizer who created the event or admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Event deleted successfully",
  "data": null
}
```

---

## 3. Ticket Management Endpoints

### 3.1 Create Ticket
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/events/{event_id}/tickets`  
**Auth:** Bearer Token required (organizer or admin)

**Request Body:**
```json
{
  "event_id": 1,
  "type": "VIP",
  "price": 150.00,
  "quantity": 100
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Ticket created successfully",
    "data": {
        "event_id": "1",
        "type": "VIP",
        "price": "150.00",
        "quantity": "100",
        "updated_at": "2026-03-18T13:38:27.000000Z",
        "created_at": "2026-03-18T13:38:27.000000Z",
        "id": 1
    }
}
```

### 3.2 Get Event Tickets
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/events/{event_id}/tickets`  
**Auth:** Bearer Token required

**Success Response (200):**
```json
{
    "success": true,
    "message": "Tickets retrieved successfully",
    "data": [
        {
            "id": 1,
            "event_id": 1,
            "type": "VIP",
            "price": "150.00",
            "quantity": 100,
            "created_at": "2026-03-18T13:38:27.000000Z",
            "updated_at": "2026-03-18T13:38:27.000000Z",
            "deleted_at": null
        },
        {
            "id": 2,
            "event_id": 1,
            "type": "Economy",
            "price": "50.00",
            "quantity": 200,
            "created_at": "2026-03-18T13:38:58.000000Z",
            "updated_at": "2026-03-18T13:38:58.000000Z",
            "deleted_at": null
        }
    ]
}
```

### 3.3 Update Ticket
**Method:** PUT/PATCH  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{id}`  
**Auth:** Bearer Token required (organizer or admin)

**Request Body:**
```json
{
  "price": 175.00,
  "quantity": 120
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Ticket updated successfully",
    "data": {
        "id": 1,
        "event_id": 1,
        "type": "VIP",
        "price": "150.00",
        "quantity": 100,
        "created_at": "2026-03-18T13:38:27.000000Z",
        "updated_at": "2026-03-18T13:38:27.000000Z",
        "deleted_at": null,
        "event": {
            "id": 1,
            "title": "Summer Music Festival",
            "description": "A great outdoor music festival",
            "date": "2026-07-15T18:00:00.000000Z",
            "location": "Central Park, New York",
            "created_by": 2,
            "created_at": "2026-03-18T13:26:05.000000Z",
            "updated_at": "2026-03-18T13:26:05.000000Z",
            "deleted_at": null
        }
    }
}
```

### 3.4 Delete Ticket
**Method:** DELETE  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{id}`  
**Auth:** Bearer Token required (organizer or admin)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Ticket deleted successfully",
  "data": null
}
```

---

## 4. Booking System Endpoints

### 4.1 Create Booking
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/tickets/{ticket}/bookings`  
**Auth:** Bearer Token required (customer)

**Request Body:**
```json
{
  "quantity": 2
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Booking created successfully",
    "data": {
        "user_id": 1,
        "ticket_id": "1",
        "quantity": "2",
        "status": "pending",
        "updated_at": "2026-03-18T13:49:24.000000Z",
        "created_at": "2026-03-18T13:49:24.000000Z",
        "id": 1
    }
}
```

### 4.2 Get User Bookings
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/bookings`  
**Auth:** Bearer Token required

**Query Parameters:**
- `status` (optional): Filter by status (pending, confirmed, cancelled)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "ticket_id": 1,
            "quantity": 2,
            "status": "pending",
            "created_at": "2026-03-18T13:49:24.000000Z",
            "updated_at": "2026-03-18T13:49:24.000000Z",
            "deleted_at": null,
            "ticket": {
                "id": 1,
                "event_id": 1,
                "type": "VIP",
                "price": "150.00",
                "quantity": 100,
                "created_at": "2026-03-18T13:38:27.000000Z",
                "updated_at": "2026-03-18T13:38:27.000000Z",
                "deleted_at": null,
                "event": {
                    "id": 1,
                    "title": "Summer Music Festival",
                    "description": "A great outdoor music festival",
                    "date": "2026-07-15T18:00:00.000000Z",
                    "location": "Central Park, New York",
                    "created_by": 2,
                    "created_at": "2026-03-18T13:26:05.000000Z",
                    "updated_at": "2026-03-18T13:26:05.000000Z",
                    "deleted_at": null
                }
            }
        }
    ]
}
```

### 4.3 Get Single Booking
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{id}`  
**Auth:** Bearer Token required (booking owner or admin)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Booking retrieved successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "ticket_id": 1,
        "quantity": 2,
        "status": "pending",
        "created_at": "2026-03-18T13:49:24.000000Z",
        "updated_at": "2026-03-18T13:49:24.000000Z",
        "deleted_at": null,
        "ticket": {
            "id": 1,
            "event_id": 1,
            "type": "VIP",
            "price": "150.00",
            "quantity": 100,
            "created_at": "2026-03-18T13:38:27.000000Z",
            "updated_at": "2026-03-18T13:38:27.000000Z",
            "deleted_at": null,
            "event": {
                "id": 1,
                "title": "Summer Music Festival",
                "description": "A great outdoor music festival",
                "date": "2026-07-15T18:00:00.000000Z",
                "location": "Central Park, New York",
                "created_by": 2,
                "created_at": "2026-03-18T13:26:05.000000Z",
                "updated_at": "2026-03-18T13:26:05.000000Z",
                "deleted_at": null
            }
        },
        "payment": null
    }
}
```

### 4.4 Cancel Booking
**Method:** PUT  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{id}/cancel`  
**Auth:** Bearer Token required (booking owner or admin)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Booking cancelled successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "ticket_id": 1,
        "quantity": 2,
        "status": "cancelled",
        "created_at": "2026-03-18T13:49:24.000000Z",
        "updated_at": "2026-03-18T14:20:15.000000Z",
        "deleted_at": null,
        "payment": {
            "id": 1,
            "booking_id": 1,
            "amount": "300.00",
            "status": "refunded",
            "created_at": "2026-03-18T14:13:41.000000Z",
            "updated_at": "2026-03-18T14:20:15.000000Z",
            "deleted_at": null
        }
    }
}
```

### 4.5 Get All Bookings (Admin)
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/admin/bookings`  
**Auth:** Bearer Token required (admin only)

**Success Response (200):**
```json
{
    "success": true,
    "message": "All bookings retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "ticket_id": 1,
            "quantity": 2,
            "status": "pending",
            "created_at": "2026-03-18T13:49:24.000000Z",
            "updated_at": "2026-03-18T13:49:24.000000Z",
            "deleted_at": null,
            "ticket": {
                "id": 1,
                "event_id": 1,
                "type": "VIP",
                "price": "150.00",
                "quantity": 100,
                "created_at": "2026-03-18T13:38:27.000000Z",
                "updated_at": "2026-03-18T13:38:27.000000Z",
                "deleted_at": null,
                "event": {
                    "id": 1,
                    "title": "Summer Music Festival",
                    "description": "A great outdoor music festival",
                    "date": "2026-07-15T18:00:00.000000Z",
                    "location": "Central Park, New York",
                    "created_by": 2,
                    "created_at": "2026-03-18T13:26:05.000000Z",
                    "updated_at": "2026-03-18T13:26:05.000000Z",
                    "deleted_at": null
                }
            }
        }
    ]
}
```

---

## 5. Payment Processing Endpoints

### 5.1 Process Payment
**Method:** POST  
**Endpoint:** `http://localhost:8000/api/v1/bookings/{bookingId}/payment`  
**Auth:** Bearer Token required (customer)

**Request Body:**
```json
{
  "payment_method": "credit_card",
  "card_number": "4111111111111111",
  "expiry_month": "12",
  "expiry_year": "2026",
  "cvv": "123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Payment processed",
    "data": {
        "status": "success",
        "amount": 300
    }
}
```

### 5.2 Get Payment Details
**Method:** GET  
**Endpoint:** `http://localhost:8000/api/v1/payments/{id}`  
**Auth:** Bearer Token required (payment owner or admin)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Payment retrieved successfully",
    "data": {
        "id": 1,
        "booking_id": 1,
        "amount": "300.00",
        "status": "success",
        "created_at": "2026-03-18T14:13:41.000000Z",
        "updated_at": "2026-03-18T14:13:41.000000Z",
        "deleted_at": null,
        "booking": {
            "id": 1,
            "user_id": 1,
            "ticket_id": 1,
            "quantity": 2,
            "status": "confirmed",
            "created_at": "2026-03-18T13:49:24.000000Z",
            "updated_at": "2026-03-18T14:13:41.000000Z",
            "deleted_at": null
        }
    }
}
```