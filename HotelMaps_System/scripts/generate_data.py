import json
import random

# Generate new data for Motels, AirBnBs, Rentals

def gen_rooms(category, price_base):
    if category == 'motel':
        return [
            {
                "id": f"rm-{random.randint(1000,9999)}",
                "room_number": f"RM-{random.randint(101,909)}",
                "room_name": "Standard Garage Room",
                "room_type": "Drive-in",
                "price": price_base,
                "capacity": "2 Adults",
                "bed_type": "1 Queen Bed",
                "size_sqm": 20,
                "amenities": "Private Garage, Aircon, TV",
                "status": "Available",
                "total_booked": random.randint(1, 50),
                "image": "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80"
            }
        ]
    elif category == 'airbnb':
        return [
            {
                "id": f"rm-{random.randint(1000,9999)}",
                "room_number": f"Unit {random.randint(10,50)}{random.randint(1,9)}",
                "room_name": "1BR Balcony Suite",
                "room_type": "Condo",
                "price": price_base,
                "capacity": "2 Adults, 1 Child",
                "bed_type": "1 Double Bed",
                "size_sqm": 28,
                "amenities": "Kitchenette, Balcony, Netflix, Pool Access",
                "status": "Available",
                "total_booked": random.randint(5, 20),
                "image": "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80"
            }
        ]
    else: # rental
        return [
            {
                "id": f"rm-{random.randint(1000,9999)}",
                "room_number": f"Apt {random.randint(1,10)}",
                "room_name": "Studio Apartment",
                "room_type": "Apartment",
                "price": price_base,
                "capacity": "1-2 Adults",
                "bed_type": "1 Bunk or Single Bed",
                "size_sqm": 15,
                "amenities": "Shared Kitchen, Free Wi-Fi, Laundry area",
                "status": "Available",
                "total_booked": random.randint(0, 5),
                "image": "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=600&q=80"
            }
        ]

data = [
    {
        "id": "motel-sogo-mabini",
        "title": "Hotel Sogo Mabini",
        "branch_name": "Mabini Branch",
        "brand": "Hotel Sogo",
        "category": "motel",
        "categoryDisplay": "Motel / Drive-in",
        "address": "A. Mabini St, Malate, Manila",
        "city": "Manila",
        "rating": 4.1,
        "viewsCount": 1200,
        "totalBooked": 450,
        "price": 450,
        "price_from": 450,
        "averageRate": 800,
        "availableRoomsCount": 5,
        "durationLabel": "3 hrs",
        "rateTypeLabel": "SHORT STAY",
        "lat": 14.5714,
        "lng": 120.9839,
        "image": "https://images.unsplash.com/photo-1596436889106-be35e843f974?auto=format&fit=crop&w=800&q=80",
        "tags": ["Drive-in", "Wash Rate", "24/7"],
        "statusText": "Walk-In Ready",
        "statusBadgeClass": "status-walkin",
        "instant": True,
        "rooms": gen_rooms('motel', 450)
    },
    {
        "id": "airbnb-light-smdc",
        "title": "SMDC Light Residences Condo",
        "branch_name": "Tower 1",
        "brand": "SMDC",
        "category": "airbnb",
        "categoryDisplay": "AirBnB / Staycation",
        "address": "EDSA cor Madison St, Mandaluyong",
        "city": "Mandaluyong",
        "rating": 4.6,
        "viewsCount": 850,
        "totalBooked": 120,
        "price": 1800,
        "price_from": 1800,
        "averageRate": 2200,
        "availableRoomsCount": 2,
        "durationLabel": "night",
        "rateTypeLabel": "DAILY RATE",
        "lat": 14.5739,
        "lng": 121.0483,
        "image": "https://images.unsplash.com/photo-1502672260266-1c1e5250ad11?auto=format&fit=crop&w=800&q=80",
        "tags": ["Pool Access", "Mall Connected", "Fast Wi-Fi"],
        "statusText": "Available",
        "statusBadgeClass": "status-walkin",
        "instant": True,
        "rooms": gen_rooms('airbnb', 1800)
    },
    {
        "id": "rental-bgc-bedspace",
        "title": "BGC Affordable Bedspace",
        "branch_name": "Pitogo Branch",
        "brand": "Local Rentals",
        "category": "rental",
        "categoryDisplay": "Short-Term Rental",
        "address": "Pitogo, Makati (near BGC)",
        "city": "Makati",
        "rating": 4.2,
        "viewsCount": 420,
        "totalBooked": 60,
        "price": 4500,
        "price_from": 4500,
        "averageRate": 4500,
        "availableRoomsCount": 4,
        "durationLabel": "month",
        "rateTypeLabel": "MONTHLY RATE",
        "lat": 14.5570,
        "lng": 121.0450,
        "image": "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80",
        "tags": ["Budget", "Near BGC", "Shared Kitchen"],
        "statusText": "Available",
        "statusBadgeClass": "status-walkin",
        "instant": True,
        "rooms": gen_rooms('rental', 4500)
    }
]

with open('c:/Users/raz3d/OneDrive/Documents/HotelMaps_System/data/stays_inventory.json', 'w') as f:
    json.dump(data, f, indent=4)
