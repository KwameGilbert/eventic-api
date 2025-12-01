<?php
return function ($app): void {
    // Define API routes here. This file is responsible for registering all API endpoints.
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    // Map route prefixes to their router files
    $routeMap = [
        '/v1/users' => ROUTE . 'v1/UserRoute.php',
        '/v1/auth' => ROUTE . 'v1/AuthRoute.php',
        '/v1/organizers' => ROUTE . 'v1/OrganizerRoute.php',
        '/v1/attendees' => ROUTE . 'v1/AttendeeRoute.php',
        // '/v1/hotels' => ROUTE . 'v1/HotelRoute.php',
        // '/v1/rooms' => ROUTE . 'v1/RoomRoute.php',
        // '/v1/customers' => ROUTE . 'v1/CustomerRoute.php',
        // '/v1/bookings' => ROUTE . 'v1/BookingRoute.php',
        // '/v1/payments' => ROUTE . 'v1/PaymentRoute.php',
        // Add more routes as needed
    ];

    $loaded = false;
    // Check if the request matches any of our defined prefixes
    foreach ($routeMap as $prefix => $routerFile) {
        if (strpos($requestUri, $prefix) === 0) {
            // Load only the matching router
            if (file_exists($routerFile)) {
                (require_once $routerFile)($app);
                $loaded = true;
            }
        }
    }

    // If no specific router was loaded, load all routers as fallback
    if (!$loaded) {
        foreach ($routeMap as $routerFile) {
            if (file_exists($routerFile)) {
                (require_once $routerFile)($app);
            }
        }
    };
};
