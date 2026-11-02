-- ============================================================
-- Migration: Add commuter & hourly rate data to existing hotels
-- MetroManila walk-in motel / Sogo branches enrichment
-- Date: October 2026
-- ============================================================

-- Step 1: Update hotel_rooms to set rate_type and duration for Sogo rooms
UPDATE hotel_rooms SET rate_type = 'hourly', duration = '3 Hours'
WHERE hotel_id LIKE 'sogo-%' AND room_type IN ('Standard Room','Economy Room');

UPDATE hotel_rooms SET rate_type = 'hourly', duration = '6 Hours'
WHERE hotel_id LIKE 'sogo-%' AND room_type IN ('Deluxe Room','Premium Room');

UPDATE hotel_rooms SET rate_type = 'nightly', duration = '12-24 Hours'
WHERE hotel_id LIKE 'sogo-%' AND room_type IN ('Executive Suite','Regency Suite');

-- Step 2: Update motel/sogo hotels with commuter data and hourly_rates JSON
-- Sogo Cubao (Aurora Blvd) - near Araneta Center / MRT Cubao
UPDATE hotels SET
    nearest_terminal = 'Araneta Center Bus Terminal / MRT Cubao',
    transit_type = 'MRT-3 Line / P2P Bus',
    walk_time = '3-5 mins walk',
    hourly_rates = '{"2hr":550,"3hr":750,"6hr":1150,"12hr":1650,"24hr":2450}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-cubao-aurora';

-- Sogo Pasay Rotonda - near EDSA Pasay / Coastal Mall Terminal
UPDATE hotels SET
    nearest_terminal = 'EDSA Pasay / Coastal Mall Bus Terminal',
    transit_type = 'Busway / P2P',
    walk_time = '2-4 mins walk',
    hourly_rates = '{"2hr":500,"3hr":700,"6hr":1100,"12hr":1600,"24hr":2350}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-pasay-rotonda';

-- Sogo Makati Guadalupe - near EDSA Guadalupe MRT
UPDATE hotels SET
    nearest_terminal = 'EDSA Guadalupe MRT-3 Station',
    transit_type = 'MRT-3 Line',
    walk_time = '5 mins walk',
    hourly_rates = '{"2hr":600,"3hr":800,"6hr":1250,"12hr":1800,"24hr":2650}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-makati-guadalupe';

-- Sogo QC Balintawak - near Balintawak Bus Terminal (North bound)
UPDATE hotels SET
    nearest_terminal = 'Balintawak Bus Terminal (North EDSA)',
    transit_type = 'LRT-1 Balintawak / North Bus',
    walk_time = '4-6 mins walk',
    hourly_rates = '{"2hr":500,"3hr":700,"6hr":1050,"12hr":1500,"24hr":2200}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-qc-balintawak';

-- Sogo QC Fairview - near Fairview Terminal
UPDATE hotels SET
    nearest_terminal = 'Fairview Bus Terminal',
    transit_type = 'Fairview - Quiapo Busway',
    walk_time = '5-8 mins walk',
    hourly_rates = '{"2hr":500,"3hr":700,"6hr":1050,"12hr":1500,"24hr":2200}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-qc-fairview';

-- Sogo QC Avenue - near Quezon Avenue MRT
UPDATE hotels SET
    nearest_terminal = 'Quezon Avenue MRT-3 Station',
    transit_type = 'MRT-3 Line',
    walk_time = '5 mins walk',
    hourly_rates = '{"2hr":550,"3hr":750,"6hr":1100,"12hr":1600,"24hr":2350}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-qc-avenue';

-- Sogo Manila Avenida - near LRT Recto / Quiapo
UPDATE hotels SET
    nearest_terminal = 'LRT-1 Recto Station / Quiapo Hub',
    transit_type = 'LRT-1 Line / Jeepney Hub',
    walk_time = '2-3 mins walk',
    hourly_rates = '{"2hr":450,"3hr":600,"6hr":950,"12hr":1380,"24hr":2080}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-manila-avenida';

-- Sogo Manila Malate - near Quirino / Pedro Gil LRT
UPDATE hotels SET
    nearest_terminal = 'LRT-1 Quirino / Pedro Gil Station',
    transit_type = 'LRT-1 Line',
    walk_time = '7 mins walk',
    hourly_rates = '{"2hr":450,"3hr":600,"6hr":950,"12hr":1350,"24hr":2050}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-manila-malate';

-- Sogo Alabang South - near Alabang / Zapote Terminal
UPDATE hotels SET
    nearest_terminal = 'Alabang Terminal (Muntinlupa)',
    transit_type = 'SLEX / Alabang Bus Loops',
    walk_time = '5-8 mins walk',
    hourly_rates = '{"2hr":480,"3hr":650,"6hr":1000,"12hr":1450,"24hr":2100}',
    duration_label = '3-Hour Wash Rate'
WHERE id = 'sogo-alabang-south';

-- Step 3: Add 5 new Metro Manila motel walk-in venues near commuter hubs
-- (These will be added to stays_inventory.json since they may not be in the DB hotels table)

-- Victoria Court Hillcrest (near Quezon City Bus)
INSERT IGNORE INTO hotels (id, title, brand, category, category_display, address, city, rating, price_from, lat, lng, image, tags, status_text, status_badge, branch_name, nearest_terminal, transit_type, walk_time, hourly_rates, duration_label)
VALUES (
    'motel-victoria-court-qc',
    'Victoria Court - Hillcrest',
    'Victoria Court Hotels',
    'sogo',
    'Themed Motel / Drive-in',
    'Hillcrest Road, Quezon City',
    'Quezon City',
    4.5, 680.00,
    14.6384, 121.0453,
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
    'Jacuzzi,Themed Rooms,Drive-in Garage,Videoke',
    'Rooms Available Now', 'status-walkin',
    'Hillcrest QC Branch',
    'Cubao / Araneta MRT Station',
    'MRT-3 / Jeepney',
    '8 mins walk',
    '{"2hr":680,"3hr":900,"6hr":1380,"12hr":1980,"24hr":2900}',
    '3-Hour Themed Rate'
);

-- Eurotel Pedro Gil - near LRT Pedro Gil
INSERT IGNORE INTO hotels (id, title, brand, category, category_display, address, city, rating, price_from, lat, lng, image, tags, status_text, status_badge, branch_name, nearest_terminal, transit_type, walk_time, hourly_rates, duration_label)
VALUES (
    'motel-eurotel-pedro-gil',
    'Eurotel Pedro Gil',
    'Eurotel Hotels',
    'sogo',
    'Budget Hotel / Short Stay',
    'Pedro Gil St., Ermita, Manila',
    'Historic Manila',
    4.3, 540.00,
    14.5764, 120.9835,
    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=80',
    'Walk-in Ready,Near LRT,24/7 Front Desk,Budget Friendly',
    'Rooms Available Now', 'status-walkin',
    'Pedro Gil Ermita Branch',
    'LRT-1 Pedro Gil Station',
    'LRT-1 Line',
    '3 mins walk',
    '{"2hr":540,"3hr":700,"6hr":1050,"12hr":1500,"24hr":2200}',
    '3-Hour Stay Rate'
);

-- Astrotel Cubao - near Cubao terminals
INSERT IGNORE INTO hotels (id, title, brand, category, category_display, address, city, rating, price_from, lat, lng, image, tags, status_text, status_badge, branch_name, nearest_terminal, transit_type, walk_time, hourly_rates, duration_label)
VALUES (
    'motel-astrotel-cubao',
    'Astrotel Cubao',
    'Astrotel Hotels',
    'sogo',
    'Budget Hotel / Short Stay',
    'EDSA, Araneta Center, Cubao, QC',
    'Quezon City',
    4.2, 500.00,
    14.6172, 121.0543,
    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',
    'Walk-in Ready,MRT Access,24/7 Room Service,Near P2P Bus',
    'Rooms Available Now', 'status-walkin',
    'Cubao EDSA Branch',
    'Araneta Center - Cubao MRT / LRT Station',
    'MRT-3 / LRT-2 / P2P Bus',
    '2 mins walk',
    '{"2hr":500,"3hr":680,"6hr":1020,"12hr":1450,"24hr":2100}',
    '3-Hour Stay Rate'
);

-- Step 4: Add hourly rooms for new motel branches
INSERT IGNORE INTO hotel_rooms (id, hotel_id, room_number, room_name, room_type, price, capacity, bed_type, size_sqm, amenities, status, total_booked, image, rate_type, duration)
VALUES
-- Victoria Court QC rooms
('rm-vc-qc-2hr', 'motel-victoria-court-qc', '102', 'Standard Room (2-Hour Wash Rate)', 'Short Stay', 680.00, '2 Guests', 'Queen Bed', 22, 'Hot/Cold Shower, LED TV with Cable, Air-Conditioned, Sanitized Linen, Safe Box', 'Available', 150, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80', 'hourly', '2 Hours'),
('rm-vc-qc-6hr', 'motel-victoria-court-qc', '205', 'Deluxe Room (6-Hour Mid Stay)', 'Mid Stay', 1380.00, '2 Guests', 'King Bed', 28, 'Mini Refrigerator, Private Toilet, Free Wi-Fi, 24/7 Room Service, Bathrobe', 'Available', 80, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80', 'hourly', '6 Hours'),
('rm-vc-qc-themed', 'motel-victoria-court-qc', '301', 'Jacuzzi Themed Suite (Overnight)', 'Themed Suite', 2900.00, '2 Guests', 'King Bed', 40, 'In-Room Jacuzzi, Themed Décor, Private Garage, Videoke System, Mood Lighting', 'Available', 40, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80', 'nightly', '12-24 Hours'),

-- Eurotel Pedro Gil rooms
('rm-euro-pg-3hr', 'motel-eurotel-pedro-gil', '104', 'Standard Studio (3-Hour Rate)', 'Short Stay', 700.00, '2 Guests', 'Double Bed', 20, 'Air-Conditioned, Hot Shower, LED TV, Free Wi-Fi, Clean Towels', 'Available', 200, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80', 'hourly', '3 Hours'),
('rm-euro-pg-6hr', 'motel-eurotel-pedro-gil', '201', 'Euro Suite (6-Hour Stay)', 'Suite', 1050.00, '2 Guests', 'Queen Bed', 25, 'Work Desk, 43in TV, Hot Shower, Mini Bar, Coffee/Tea Set', 'Available', 90, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80', 'hourly', '6 Hours'),
('rm-euro-pg-over', 'motel-eurotel-pedro-gil', '305', 'Deluxe Room (Overnight)', 'Deluxe', 2200.00, '2 Guests', 'King Bed', 32, 'Free Breakfast, Jacuzzi Tub, Bathrobe, Toiletries Set, 55in Smart TV', 'Available', 50, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80', 'nightly', '12-24 Hours'),

-- Astrotel Cubao rooms
('rm-ast-cub-2hr', 'motel-astrotel-cubao', '101', 'Standard Room (2-Hour Short Stay)', 'Short Stay', 500.00, '2 Guests', 'Double Bed', 18, 'Air-Conditioned, Hot Shower, Cable TV, Free Wi-Fi, Sanitized Room', 'Available', 350, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80', 'hourly', '2 Hours'),
('rm-ast-cub-6hr', 'motel-astrotel-cubao', '201', 'Premium Room (6-Hour Stay)', 'Premium', 1020.00, '2 Guests', 'Queen Bed', 24, 'Mini Refrigerator, LED TV, Hot Shower, Free Wi-Fi, En Suite Bath', 'Available', 120, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80', 'hourly', '6 Hours'),
('rm-ast-cub-over', 'motel-astrotel-cubao', '301', 'Deluxe Room (Overnight)', 'Deluxe', 2100.00, '2 Guests', 'King Bed', 30, 'Free Breakfast, Bathrobe, Slippers, Coffee Set, 50in Smart TV', 'Available', 60, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80', 'nightly', '12-24 Hours');
