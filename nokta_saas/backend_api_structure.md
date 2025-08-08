# Backend API Structure for Nokta POS

## API Endpoints

### Authentication
- POST /api/auth/login
- POST /api/auth/register
- POST /api/auth/logout
- POST /api/auth/refresh
- POST /api/auth/verify-2fa

### Products
- GET /api/products
- GET /api/products/:id
- POST /api/products
- PUT /api/products/:id
- DELETE /api/products/:id
- POST /api/products/bulk-import

### Orders
- GET /api/orders
- GET /api/orders/:id
- POST /api/orders
- PUT /api/orders/:id
- PUT /api/orders/:id/status
- GET /api/orders/analytics

### Customers
- GET /api/customers
- GET /api/customers/:id
- POST /api/customers
- PUT /api/customers/:id
- GET /api/customers/:id/orders

### Drivers
- GET /api/drivers
- GET /api/drivers/:id
- POST /api/drivers
- PUT /api/drivers/:id
- GET /api/drivers/:id/deliveries
- PUT /api/drivers/:id/location

### Reports
- GET /api/reports/sales
- GET /api/reports/inventory
- GET /api/reports/revenue
- GET /api/reports/customers
- GET /api/reports/products

### Real-time (WebSocket)
- /ws/orders
- /ws/kitchen
- /ws/driver-tracking
- /ws/notifications

## Database Schema

### Tables
1. tenants
2. users
3. branches
4. products
5. categories
6. orders
7. order_items
8. customers
9. drivers
10. payments
11. inventory
12. reports
13. settings
