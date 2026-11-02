const fs = require('fs');
const path = require('path');

const stays = [
  // ==========================================
  // MOTELS (Hourly, Wash Rates, Drive-in)
  // ==========================================
  {
    id: "motel-sogo-mabini",
    title: "Hotel Sogo Mabini",
    branch_name: "Mabini Branch",
    brand: "Hotel Sogo",
    category: "motel",
    categoryDisplay: "Motel / Drive-in",
    address: "A. Mabini St, Malate, Manila",
    city: "Historic Manila",
    rating: 4.2,
    ratingCount: 520,
    viewsCount: 1420,
    totalBooked: 390,
    price: 450,
    price_from: 450,
    durationLabel: "3-Hour Wash Rate",
    rateTypeLabel: "HOURLY WASH RATE",
    rateType: "hourly",
    lat: 14.5712,
    lng: 120.9855,
    image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80",
    tags: ["24/7 Wash Rate", "Private Drive-In", "Discreet Check-In", "Soundproof"],
    statusText: "Rooms Available Now",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "sogo-mabini-3hr",
        room_number: "204",
        room_name: "Premium Wash Room (3 Hours)",
        room_type: "Short Stay",
        price: 450,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 18,
        amenities: "Hot & Cold Shower, LED TV with Cable, Air-Conditioned, Sanitized Linen",
        status: "Available",
        total_booked: 240,
        image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "sogo-mabini-6hr",
        room_number: "208",
        room_name: "Executive Room (6 Hours)",
        room_type: "Mid Stay",
        price: 750,
        rate_type: "hourly",
        duration: "6 Hours",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 22,
        amenities: "Mini Refrigerator, Private Toilet, Free Wi-Fi, 24/7 Room Service",
        status: "Available",
        total_booked: 110,
        image: "https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "sogo-mabini-overnight",
        room_number: "312",
        room_name: "Deluxe Overnight Garage Suite",
        room_type: "Overnight",
        price: 1350,
        rate_type: "nightly",
        duration: "12-24 Hours",
        capacity: "2 Guests",
        bed_type: "King Bed",
        size_sqm: 28,
        amenities: "Direct Garage Entrance, Bathtub, High-Speed Wi-Fi, Breakfast Option",
        status: "Available",
        total_booked: 40,
        image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "bedroom",
          name: "Executive Suite Bed",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Comfortable Queen-size bed with soft mood backlighting and room service menu",
          hotspots: [
            { targetRoomId: "bathroom", label: "To Shower & Spa", yaw: 85, pitch: -6 },
            { targetRoomId: "garage", label: "To Private Garage Door", yaw: 215, pitch: -2 }
          ]
        },
        {
          id: "bathroom",
          name: "Private Wash & Shower",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Sparkling clean ceramic wash area with hot rainfall shower",
          hotspots: [
            { targetRoomId: "bedroom", label: "Return to Suite", yaw: 180, pitch: -4 }
          ]
        },
        {
          id: "garage",
          name: "Private Drive-In Garage",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Direct roller-shutter garage bay offering 100% privacy and contactless entry",
          hotspots: [
            { targetRoomId: "bedroom", label: "Step Upstairs into Suite", yaw: 35, pitch: 8 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-vc-hillcrest",
    title: "Victoria Court Hillcrest",
    branch_name: "Hillcrest Branch",
    brand: "Victoria Court",
    category: "motel",
    categoryDisplay: "Themed Motel",
    address: "Hillcrest Drive, Pasig City",
    city: "Ortigas Center",
    rating: 4.5,
    ratingCount: 880,
    viewsCount: 2310,
    totalBooked: 720,
    price: 680,
    price_from: 680,
    durationLabel: "3-Hour Themed Rate",
    rateTypeLabel: "HOURLY THEMED RATE",
    rateType: "hourly",
    lat: 14.5772,
    lng: 121.0631,
    image: "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80",
    tags: ["Themed Suites", "Jacuzzi", "Drive-In Garage", "Billiards & Bar"],
    statusText: "Themed Suites Ready",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "vc-hillcrest-3hr",
        room_number: "Suite 105",
        room_name: "Austin Powers Themed Suite (3 Hours)",
        room_type: "Themed Short Stay",
        price: 680,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2-4 Guests",
        bed_type: "Circular King Bed",
        size_sqm: 35,
        amenities: "Whirlpool Jacuzzi, Mood Lights, Party Sound System, Private Bar",
        status: "Available",
        total_booked: 380,
        image: "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "vc-hillcrest-overnight",
        room_number: "Suite 201",
        room_name: "Moulin Rouge Party Suite (Overnight)",
        room_type: "Party Suite",
        price: 2450,
        rate_type: "nightly",
        duration: "Overnight (12h)",
        capacity: "4-6 Guests",
        bed_type: "Custom King + Lounge",
        size_sqm: 55,
        amenities: "Private Jacuzzi, Karaoke System, Drive-In Double Garage, Dining Area",
        status: "Available",
        total_booked: 340,
        image: "https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "lounge",
          name: "Themed Party Lounge",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Vibrant retro-styled party lounge equipped with neon mood lights and plush seating",
          hotspots: [
            { targetRoomId: "jacuzzi", label: "To Jacuzzi Spa Area", yaw: 110, pitch: -4 },
            { targetRoomId: "bedroom", label: "To Circular Bed", yaw: 260, pitch: -3 }
          ]
        },
        {
          id: "jacuzzi",
          name: "Whirlpool Jacuzzi Tub",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Heated hydro-jet whirlpool jacuzzi with warm ambient backlights",
          hotspots: [
            { targetRoomId: "lounge", label: "Back to Lounge", yaw: 0, pitch: -2 },
            { targetRoomId: "bedroom", label: "Direct to Bedroom", yaw: 175, pitch: -2 }
          ]
        },
        {
          id: "bedroom",
          name: "Circular King Suite",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Ultra-comfy circular king bed with surround speakers and mirror ceiling accents",
          hotspots: [
            { targetRoomId: "lounge", label: "To Party Lounge", yaw: 70, pitch: -5 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-sogo-pasay",
    title: "Hotel Sogo Pasay Rotonda",
    branch_name: "Pasay Rotonda Branch",
    brand: "Hotel Sogo",
    category: "motel",
    categoryDisplay: "Motel / Transit Stay",
    address: "EDSA cor. Taft Avenue, Pasay City",
    city: "Pasay & Manila Bay",
    rating: 4.1,
    ratingCount: 940,
    viewsCount: 3100,
    totalBooked: 890,
    price: 430,
    price_from: 430,
    durationLabel: "3-Hour Transit Rate",
    rateTypeLabel: "HOURLY TRANSIT RATE",
    rateType: "hourly",
    lat: 14.5378,
    lng: 121.0014,
    image: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80",
    tags: ["Near LRT/MRT", "24/7 Wash Rates", "Fast Transit Rest", "Discreet"],
    statusText: "Walk-in Open 24/7",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "sogo-pasay-3hr",
        room_number: "112",
        room_name: "Transit Rest Room (3 Hours)",
        room_type: "Short Stay",
        price: 430,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 16,
        amenities: "Air conditioning, Cable TV, Private Bath, Clean Towels & Soap",
        status: "Available",
        total_booked: 540,
        image: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "sogo-pasay-12hr",
        room_number: "220",
        room_name: "Deluxe 12-Hour Layover Room",
        room_type: "Half-Day Stay",
        price: 980,
        rate_type: "hourly",
        duration: "12 Hours",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 22,
        amenities: "Hot Shower, Room Food Service, Fast Wi-Fi, Luggage Storage",
        status: "Available",
        total_booked: 350,
        image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "bedroom",
          name: "Transit Rest Chamber",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Clean, sound-insulated bedroom for transit travelers and quick refresh",
          hotspots: [
            { targetRoomId: "bathroom", label: "To Hot Shower", yaw: 90, pitch: -5 }
          ]
        },
        {
          id: "bathroom",
          name: "Refresh Bathroom",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Sanitized bathroom with hot & cold shower and fresh bath amenities",
          hotspots: [
            { targetRoomId: "bedroom", label: "Back to Bedroom", yaw: 270, pitch: -5 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-vc-cuneta",
    title: "Victoria Court Cuneta",
    branch_name: "Cuneta Branch",
    brand: "Victoria Court",
    category: "motel",
    categoryDisplay: "Drive-In Motel",
    address: "Cuneta Avenue, Pasay City",
    city: "Pasay & Manila Bay",
    rating: 4.4,
    ratingCount: 610,
    viewsCount: 1890,
    totalBooked: 510,
    price: 620,
    price_from: 620,
    durationLabel: "3-Hour Drive-In",
    rateTypeLabel: "HOURLY DRIVE-IN RATE",
    rateType: "hourly",
    lat: 14.5362,
    lng: 120.9942,
    image: "https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=800&q=80",
    tags: ["Private Garage", "Jacuzzi", "Discreet Food Pass", "24/7 Gate"],
    statusText: "Garage Bays Open",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "vc-cuneta-standard",
        room_number: "Bay 12",
        room_name: "Standard Drive-In Garage Room (3 Hours)",
        room_type: "Drive-In Wash",
        price: 620,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 30,
        amenities: "Ground Floor Private Garage, Direct Staircase, LED TV, Aircon",
        status: "Available",
        total_booked: 320,
        image: "https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "vc-cuneta-suite",
        room_number: "Bay 18",
        room_name: "VIP Jacuzzi Garage Suite (Overnight)",
        room_type: "VIP Suite",
        price: 1980,
        rate_type: "nightly",
        duration: "12-Hour Stay",
        capacity: "2 Guests",
        bed_type: "King Bed",
        size_sqm: 48,
        amenities: "Whirlpool Jacuzzi, Private Garage, Netflix, Food Hatch Delivery",
        status: "Available",
        total_booked: 190,
        image: "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "garage",
          name: "Private Ground Garage",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Private direct-access vehicular bay with roll-up security door",
          hotspots: [
            { targetRoomId: "suite", label: "Take Private Stairs to Suite", yaw: 45, pitch: 10 }
          ]
        },
        {
          id: "suite",
          name: "Deluxe Suite Room",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Elegantly finished private suite with king-size bed and smart entertainment",
          hotspots: [
            { targetRoomId: "garage", label: "Downstairs to Garage", yaw: 220, pitch: -8 },
            { targetRoomId: "spa", label: "To Jacuzzi Corner", yaw: 110, pitch: -3 }
          ]
        },
        {
          id: "spa",
          name: "Jacuzzi Spa Corner",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Deep bubbling jacuzzi tub with mood illumination",
          hotspots: [
            { targetRoomId: "suite", label: "Back to Bed Area", yaw: 270, pitch: -4 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-eurotel-pedrogil",
    title: "Eurotel Pedro Gil",
    branch_name: "Pedro Gil Branch",
    brand: "Eurotel",
    category: "motel",
    categoryDisplay: "European Styled Budget Motel",
    address: "616 Pedro Gil St, Ermita, Manila",
    city: "Historic Manila",
    rating: 4.3,
    ratingCount: 710,
    viewsCount: 1950,
    totalBooked: 620,
    price: 520,
    price_from: 520,
    durationLabel: "3-Hour Rest Rate",
    rateTypeLabel: "HOURLY REST RATE",
    rateType: "hourly",
    lat: 14.5771,
    lng: 120.9882,
    image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80",
    tags: ["Euro-Bedding", "Wi-Fi", "Massages", "Breakfast Included"],
    statusText: "Rooms Available",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "eurotel-std-3hr",
        room_number: "Euro 304",
        room_name: "Euro Standard (3 Hours)",
        room_type: "Short Stay",
        price: 520,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 20,
        amenities: "European Duvet, LED TV, Free Wi-Fi, Hot Shower",
        status: "Available",
        total_booked: 410,
        image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "eurotel-studio-overnight",
        room_number: "Euro 408",
        room_name: "Euro Studio (Overnight Stay)",
        room_type: "Overnight",
        price: 1550,
        rate_type: "nightly",
        duration: "Overnight",
        capacity: "2 Guests",
        bed_type: "King Bed",
        size_sqm: 26,
        amenities: "Buffet Breakfast voucher, Hairdryer, Safe, High-speed fiber",
        status: "Available",
        total_booked: 210,
        image: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "room",
          name: "Euro Deluxe Bedchamber",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Clean European styled room with warm wooden details and comfortable bedding",
          hotspots: [
            { targetRoomId: "bath", label: "To Private Bath", yaw: 100, pitch: -6 }
          ]
        },
        {
          id: "bath",
          name: "Sanitized Shower Area",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Modern bathroom with pressurized hot water shower",
          hotspots: [
            { targetRoomId: "room", label: "Return to Room", yaw: 280, pitch: -4 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-sogo-cubao",
    title: "Hotel Sogo Cubao",
    branch_name: "Cubao Branch",
    brand: "Hotel Sogo",
    category: "motel",
    categoryDisplay: "Motel / Drive-in",
    address: "Aurora Blvd cor. EDSA, Cubao, Quezon City",
    city: "Quezon City",
    rating: 4.1,
    ratingCount: 820,
    viewsCount: 2650,
    totalBooked: 740,
    price: 460,
    price_from: 460,
    durationLabel: "3-Hour Wash Rate",
    rateTypeLabel: "HOURLY WASH RATE",
    rateType: "hourly",
    lat: 14.6234,
    lng: 121.0543,
    image: "https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80",
    tags: ["Aurora Blvd", "24h Garage", "Affordable", "Soundproof"],
    statusText: "Rooms Available",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "sogo-cubao-3hr",
        room_number: "C-102",
        room_name: "Deluxe Short-Stay (3 Hours)",
        room_type: "Short Stay",
        price: 460,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 18,
        amenities: "Air-conditioned, Cable TV, Hot Shower, 24/7 Room Service",
        status: "Available",
        total_booked: 490,
        image: "https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "sogo-cubao-garage",
        room_number: "G-08",
        room_name: "Garage Room (Overnight)",
        room_type: "Drive-in Garage",
        price: 1420,
        rate_type: "nightly",
        duration: "Overnight (12-24h)",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 26,
        amenities: "Direct Vehicle Garage, Free Wi-Fi, Food delivery hatch",
        status: "Available",
        total_booked: 250,
        image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "bed",
          name: "Cubao Suite Room",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Air-conditioned private room with ambient lighting",
          hotspots: [
            { targetRoomId: "bath", label: "To Bathroom", yaw: 80, pitch: -5 }
          ]
        },
        {
          id: "bath",
          name: "Private Bath",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Clean restroom with hot water shower",
          hotspots: [
            { targetRoomId: "bed", label: "Back to Bed", yaw: 260, pitch: -5 }
          ]
        }
      ]
    }
  },
  {
    id: "motel-mariposa-pasig",
    title: "Mariposa Budget Hotel",
    branch_name: "Pasig Branch",
    brand: "Mariposa",
    category: "motel",
    categoryDisplay: "Budget Motel / Drive-in",
    address: "C. Raymundo Ave, Rosario, Pasig City",
    city: "Ortigas Center",
    rating: 4.2,
    ratingCount: 390,
    viewsCount: 1120,
    totalBooked: 310,
    price: 390,
    price_from: 390,
    durationLabel: "3-Hour Wash Rate",
    rateTypeLabel: "HOURLY WASH RATE",
    rateType: "hourly",
    lat: 14.5821,
    lng: 121.0854,
    image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80",
    tags: ["Pocket-Friendly", "Wash Rate", "Free Parking", "Aircon"],
    statusText: "Walk-in Welcome",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "mariposa-wash",
        room_number: "M-14",
        room_name: "Pocket Wash Room (3 Hours)",
        room_type: "Wash Rate",
        price: 390,
        rate_type: "hourly",
        duration: "3 Hours",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 16,
        amenities: "Air conditioning, Cable TV, Private toilet & bath",
        status: "Available",
        total_booked: 210,
        image: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "mariposa-overnight",
        room_number: "M-22",
        room_name: "Standard Overnight Room",
        room_type: "Overnight",
        price: 1100,
        rate_type: "nightly",
        duration: "Overnight (12h)",
        capacity: "2 Guests",
        bed_type: "Queen Bed",
        size_sqm: 22,
        amenities: "Wi-Fi, Free parking, Bottled water, Soap & Towels",
        status: "Available",
        total_booked: 100,
        image: "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "bed",
          name: "Standard Room",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Neat and clean budget room with cozy bedding",
          hotspots: [{ targetRoomId: "bath", label: "To Bath", yaw: 90, pitch: -4 }]
        },
        {
          id: "bath",
          name: "Shower",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Clean restroom with hot shower",
          hotspots: [{ targetRoomId: "bed", label: "To Bed", yaw: 270, pitch: -4 }]
        }
      ]
    }
  },

  // ==========================================
  // AIRBNBS / CONDO STAYCATIONS (Nightly Rates)
  // ==========================================
  {
    id: "airbnb-smdc-light",
    title: "SMDC Light Residences Staycation",
    branch_name: "Tower 2 - Boni MRT",
    brand: "SMDC Light Residences",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "EDSA cor. Madison St, Barangka Ilaya, Mandaluyong City",
    city: "Mandaluyong & Pasig",
    rating: 4.8,
    ratingCount: 1240,
    viewsCount: 4500,
    totalBooked: 960,
    price: 1850,
    price_from: 1850,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY CONDO RATE",
    rateType: "nightly",
    lat: 14.5739,
    lng: 121.0481,
    image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80",
    tags: ["Direct Mall Access", "Boni MRT Linked", "Pool & Gym", "50Mbps Fiber", "Netflix 4K"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "light-deluxe-balcony",
        room_number: "Unit 3412",
        room_name: "1-Bedroom Deluxe with Skyline Balcony",
        room_type: "1-Bedroom Suite",
        price: 1850,
        rate_type: "nightly",
        duration: "per night",
        capacity: "3 Guests",
        bed_type: "Queen Bed + Sofa Bed",
        size_sqm: 28,
        amenities: "Kitchenette with Induction Cooker, Refrigerator, Microwave, 55-inch 4K TV, 50Mbps Fiber, Balcony Views",
        status: "Available",
        total_booked: 580,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "light-executive-loft",
        room_number: "Unit 4005",
        room_name: "Executive Sunset View Suite (High Floor)",
        room_type: "Executive Condo",
        price: 2300,
        rate_type: "nightly",
        duration: "per night",
        capacity: "4 Guests",
        bed_type: "King Bed + Rollaway",
        size_sqm: 32,
        amenities: "Full Kitchen, Washer/Dryer, Pool Passes Included, High-Floor Sunset Panorama",
        status: "Available",
        total_booked: 380,
        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Cozy Living Area & Kitchen",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Warm Nordic-inspired living room with plush loveseat, 4K TV, and fully equipped induction kitchen",
          hotspots: [
            { targetRoomId: "bedroom", label: "Enter Bedroom Suite", yaw: 65, pitch: -4 },
            { targetRoomId: "balcony", label: "Walk out to Skyline Balcony", yaw: 185, pitch: 1 }
          ]
        },
        {
          id: "bedroom",
          name: "Master Bedroom",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Queen posturepedic mattress, hotel-grade cotton sheets, blackout curtains, and city lights",
          hotspots: [
            { targetRoomId: "living", label: "Back to Living Area", yaw: 240, pitch: -4 },
            { targetRoomId: "bathroom", label: "To En-Suite Bathroom", yaw: 110, pitch: -2 }
          ]
        },
        {
          id: "bathroom",
          name: "Modern Bathroom",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Glass shower enclosure with water heater, bidet spray, and hair styling tools",
          hotspots: [
            { targetRoomId: "bedroom", label: "Return to Bedroom", yaw: 180, pitch: -4 }
          ]
        },
        {
          id: "balcony",
          name: "High-Rise Skyline Balcony",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Breathtaking 34th-floor views of Metro Manila skyline, outdoor bistro coffee table",
          hotspots: [
            { targetRoomId: "living", label: "Step Back Inside", yaw: 0, pitch: -4 }
          ]
        }
      ]
    }
  },
  {
    id: "airbnb-smdc-fame",
    title: "SMDC Fame Residences Staycation",
    branch_name: "Tower 1 - Central EDSA",
    brand: "SMDC Fame Residences",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "EDSA and Mayflower St, Mandaluyong City",
    city: "Mandaluyong & Pasig",
    rating: 4.7,
    ratingCount: 780,
    viewsCount: 3200,
    totalBooked: 610,
    price: 1950,
    price_from: 1950,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY CONDO RATE",
    rateType: "nightly",
    lat: 14.5802,
    lng: 121.0514,
    image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80",
    tags: ["Glitz & Glamour Theme", "Resort Pool", "Near Megamall", "PS4 Gaming"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "fame-glam-suite",
        room_number: "Unit 2218",
        room_name: "Glam 1-BR Suite with Balcony & PS4",
        room_type: "1-Bedroom Condo",
        price: 1950,
        rate_type: "nightly",
        duration: "per night",
        capacity: "3 Guests",
        bed_type: "Queen Bed",
        size_sqm: 27,
        amenities: "PlayStation 4 Console, 65-inch TV, High-speed fiber, Induction cooking, Balcony",
        status: "Available",
        total_booked: 410,
        image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "fame-studio",
        room_number: "Unit 1510",
        room_name: "Minimalist Studio Staycation",
        room_type: "Studio Condo",
        price: 1650,
        rate_type: "nightly",
        duration: "per night",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 24,
        amenities: "Netflix, Aircon, Coffee station, Work desk with monitor",
        status: "Available",
        total_booked: 200,
        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Glam Lounge & Entertainment",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Chic lounge with velvet armchair, PS4 console, and board games",
          hotspots: [
            { targetRoomId: "bedroom", label: "To Master Bedroom", yaw: 70, pitch: -3 },
            { targetRoomId: "balcony", label: "To Balcony", yaw: 190, pitch: 0 }
          ]
        },
        {
          id: "bedroom",
          name: "Cozy Bedroom Suite",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Comfortable Queen-size bed with soft warm lighting",
          hotspots: [
            { targetRoomId: "living", label: "Back to Lounge", yaw: 250, pitch: -4 }
          ]
        },
        {
          id: "balcony",
          name: "Mandaluyong City View Balcony",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Fresh breeze overlooking Mayflower strip",
          hotspots: [
            { targetRoomId: "living", label: "Go Inside", yaw: 0, pitch: -3 }
          ]
        }
      ]
    }
  },
  {
    id: "airbnb-smdc-sea",
    title: "SMDC Sea Residences Staycation",
    branch_name: "Tower C - MOA Pasay",
    brand: "SMDC Sea Residences",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "Pearl Drive cor. Sunrise Dr, Mall of Asia Complex, Pasay City",
    city: "Pasay & Manila Bay",
    rating: 4.8,
    ratingCount: 1540,
    viewsCount: 5200,
    totalBooked: 1200,
    price: 2100,
    price_from: 2100,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY CONDO RATE",
    rateType: "nightly",
    lat: 14.5329,
    lng: 120.9856,
    image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80",
    tags: ["Steps to MOA", "Olympic Pools", "Near MOA Arena", "Balcony Sunset"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "sea-poolview-suite",
        room_number: "Unit 718",
        room_name: "1-Bedroom Suite with Pool & Manila Bay View",
        room_type: "1-Bedroom Condo",
        price: 2100,
        rate_type: "nightly",
        duration: "per night",
        capacity: "4 Guests",
        bed_type: "Queen Bed + Double Pullout",
        size_sqm: 29,
        amenities: "Pool View Balcony, Complete Cookware, Refrigerator, Microwave, Hot Shower, 100Mbps Wi-Fi",
        status: "Available",
        total_booked: 780,
        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "sea-family-unit",
        room_number: "Unit 1024",
        room_name: "Family Staycation Suite with Kitchen",
        room_type: "Family Condo",
        price: 2600,
        rate_type: "nightly",
        duration: "per night",
        capacity: "5 Guests",
        bed_type: "2 Queen Beds",
        size_sqm: 35,
        amenities: "Dining Set for 4, Smart TV, Rice Cooker, Board Games, Kids Pool Pass",
        status: "Available",
        total_booked: 420,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Seaside Living Room",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Bright airy living room with dining area and sofa bed",
          hotspots: [
            { targetRoomId: "bedroom", label: "To Bed Suite", yaw: 60, pitch: -3 },
            { targetRoomId: "balcony", label: "To Pool View Balcony", yaw: 180, pitch: 0 }
          ]
        },
        {
          id: "bedroom",
          name: "Master Suite",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Serene bedroom with comfortable queen bed and ambient lighting",
          hotspots: [
            { targetRoomId: "living", label: "Back to Living Room", yaw: 240, pitch: -4 }
          ]
        },
        {
          id: "balcony",
          name: "Olympic Pool & Bay View Balcony",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Overlooks the lush tropical pool grounds of Sea Residences",
          hotspots: [
            { targetRoomId: "living", label: "Return Inside", yaw: 0, pitch: -4 }
          ]
        }
      ]
    }
  },
  {
    id: "airbnb-smdc-grass",
    title: "SMDC Grass Residences Staycation",
    brand: "SMDC Grass Residences",
    branch_name: "Tower 3 - North EDSA",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "Nueva Vizcaya St, Bgy. Sto. Cristo, Quezon City",
    city: "Quezon City",
    rating: 4.7,
    ratingCount: 1100,
    viewsCount: 3890,
    totalBooked: 840,
    price: 1750,
    price_from: 1750,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY CONDO RATE",
    rateType: "nightly",
    lat: 14.6565,
    lng: 121.0312,
    image: "https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=800&q=80",
    tags: ["Bridgeway to SM North", "6 Huge Pools", "Badminton Courts", "Netflix"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "grass-1br",
        room_number: "Unit 2814",
        room_name: "1-Bedroom Suite with SM North Bridgeway Access",
        room_type: "1-Bedroom Condo",
        price: 1750,
        rate_type: "nightly",
        duration: "per night",
        capacity: "3 Guests",
        bed_type: "Double Bed + Pullout",
        size_sqm: 26,
        amenities: "Air conditioning, Kitchenette, 43-inch Smart TV, Hot Shower, Fiber Wi-Fi",
        status: "Available",
        total_booked: 510,
        image: "https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "grass-balcony-deluxe",
        room_number: "Unit 3502",
        room_name: "Deluxe Balcony Suite with Pool View",
        room_type: "Deluxe Condo",
        price: 2100,
        rate_type: "nightly",
        duration: "per night",
        capacity: "4 Guests",
        bed_type: "Queen Bed",
        size_sqm: 30,
        amenities: "Pool View, Full Kitchenette, Refrigerator, Induction Cooker, Microwave",
        status: "Available",
        total_booked: 330,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Grass Residences Suite Lounge",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Comfortable condo interior with dining table and entertainment unit",
          hotspots: [{ targetRoomId: "bedroom", label: "To Bed Space", yaw: 70, pitch: -3 }]
        },
        {
          id: "bedroom",
          name: "Bedroom",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Cozy bedroom with blackout drapes",
          hotspots: [{ targetRoomId: "living", label: "To Living Room", yaw: 250, pitch: -4 }]
        }
      ]
    }
  },
  {
    id: "airbnb-jazz-makati",
    title: "Jazz Residences Staycation",
    branch_name: "Tower D - Bel-Air",
    brand: "Jazz Residences",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "Nicanor Garcia cor. Jupiter St, Bel-Air, Makati City",
    city: "Makati CBD",
    rating: 4.8,
    ratingCount: 1390,
    viewsCount: 4780,
    totalBooked: 1040,
    price: 2200,
    price_from: 2200,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY CONDO RATE",
    rateType: "nightly",
    lat: 14.5631,
    lng: 121.0195,
    image: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80",
    tags: ["Makati CBD", "Jazz Mall Onsite", "Lush Pools", "Hypermarket", "Netflix"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "jazz-1br-makati",
        room_number: "Unit 1904",
        room_name: "1-Bedroom Makati Skyline Suite with Balcony",
        room_type: "1-Bedroom Condo",
        price: 2200,
        rate_type: "nightly",
        duration: "per night",
        capacity: "3 Guests",
        bed_type: "Queen Bed",
        size_sqm: 29,
        amenities: "Balcony overlooking Makati skyscrapers, Fully equipped kitchen, Fast Wi-Fi, Hot Shower",
        status: "Available",
        total_booked: 690,
        image: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "jazz-studio-exec",
        room_number: "Unit 2411",
        room_name: "Executive Studio for Remote Workers",
        room_type: "Studio Condo",
        price: 1850,
        rate_type: "nightly",
        duration: "per night",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 23,
        amenities: "Ergonomic work desk, 100Mbps fiber, Nespresso machine, Smart TV",
        status: "Available",
        total_booked: 350,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Makati Skyline Lounge",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Stylish city condo with dedicated work desk and comfy couch",
          hotspots: [
            { targetRoomId: "bedroom", label: "To Bedroom", yaw: 65, pitch: -3 },
            { targetRoomId: "balcony", label: "To Balcony & Skyline", yaw: 180, pitch: 0 }
          ]
        },
        {
          id: "bedroom",
          name: "Bel-Air Bed Suite",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Serene bedroom with city views and cotton linen",
          hotspots: [{ targetRoomId: "living", label: "To Lounge", yaw: 240, pitch: -4 }]
        },
        {
          id: "balcony",
          name: "Makati CBD Skyline Balcony",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Night lights view of Makati CBD high-rises",
          hotspots: [{ targetRoomId: "living", label: "Step Inside", yaw: 0, pitch: -4 }]
        }
      ]
    }
  },
  {
    id: "airbnb-azure-paranaque",
    title: "Azure Urban Resort Residences",
    branch_name: "Bahamas Tower",
    brand: "Azure Urban Resort",
    category: "airbnb",
    categoryDisplay: "Condo Staycation",
    address: "Km 16 West Service Rd, Bicutan, Parañaque City",
    city: "Alabang & South",
    rating: 4.6,
    ratingCount: 1680,
    viewsCount: 5900,
    totalBooked: 1380,
    price: 2350,
    price_from: 2350,
    durationLabel: "night",
    rateTypeLabel: "NIGHTLY RESORT CONDO",
    rateType: "nightly",
    lat: 14.4851,
    lng: 121.0442,
    image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80",
    tags: ["Man-Made White Beach", "Wave Pool", "Paris Hilton Beach Club", "Balcony"],
    statusText: "Instant Booking",
    statusBadgeClass: "status-booking",
    instant: true,
    rooms: [
      {
        id: "azure-beachview",
        room_number: "Unit 1109",
        room_name: "1-Bedroom Suite with Man-Made Beach View",
        room_type: "1-Bedroom Condo",
        price: 2350,
        rate_type: "nightly",
        duration: "per night",
        capacity: "4 Guests",
        bed_type: "Queen Bed + Double Sofa Bed",
        size_sqm: 31,
        amenities: "Direct Beach & Wave Pool View Balcony, Wave pool passes, Kitchen with Microwave & Induction, Hot Shower",
        status: "Available",
        total_booked: 890,
        image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "azure-2br-family",
        room_number: "Unit 1402",
        room_name: "2-Bedroom Family Suite with Wave Pool Access",
        room_type: "2-Bedroom Condo",
        price: 3800,
        rate_type: "nightly",
        duration: "per night",
        capacity: "6 Guests",
        bed_type: "2 Queen Beds + Sofa Bed",
        size_sqm: 49,
        amenities: "2 Private Bedrooms, Full Kitchen, 6 Wave Pool Passes, Living & Dining Room",
        status: "Available",
        total_booked: 490,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "living",
          name: "Beach-Vibe Living Suite",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Relaxed tropical living room with dining nook",
          hotspots: [
            { targetRoomId: "bedroom", label: "To Bedroom Suite", yaw: 70, pitch: -3 },
            { targetRoomId: "beachbalcony", label: "To Wave Pool Balcony", yaw: 185, pitch: 0 }
          ]
        },
        {
          id: "bedroom",
          name: "White Linen Bedroom",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Cool air-conditioned master bedroom with plush cushions",
          hotspots: [{ targetRoomId: "living", label: "Back to Living Room", yaw: 240, pitch: -4 }]
        },
        {
          id: "beachbalcony",
          name: "Man-Made Beach Overlook",
          pano: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=2000&q=80",
          description: "Direct view of Azure's white sand beach and wave pool",
          hotspots: [{ targetRoomId: "living", label: "Return Indoors", yaw: 0, pitch: -4 }]
        }
      ]
    }
  },

  // ==========================================
  // SHORT-TERM RENTALS / BEDSPACES / APARTMENTS
  // (Monthly / Weekly / Student Bedspace Rates)
  // ==========================================
  {
    id: "rental-pio-del-pilar",
    title: "Pio Del Pilar Studio Apartments",
    branch_name: "Makati CBD Fringe",
    brand: "MetroLoft Rentals",
    category: "rental",
    categoryDisplay: "Studio Apartment Rental",
    address: "Arnaiz Ave cor. Washington St, Pio del Pilar, Makati City",
    city: "Makati CBD",
    rating: 4.6,
    ratingCount: 310,
    viewsCount: 1650,
    totalBooked: 290,
    price: 14000,
    price_from: 14000,
    durationLabel: "month",
    rateTypeLabel: "MONTHLY RENTAL",
    rateType: "monthly",
    lat: 14.5512,
    lng: 121.0118,
    image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80",
    tags: ["Walking distance to Ayala", "Own Electric Submeter", "CCTV 24/7", "Fiber Internet Ready"],
    statusText: "Short/Long Term Available",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "pdp-studio-monthly",
        room_number: "Apt 302",
        room_name: "Fully Furnished Studio Apartment (Monthly)",
        room_type: "Monthly Rental",
        price: 14000,
        rate_type: "monthly",
        duration: "per month",
        capacity: "2 Tenants",
        bed_type: "Double Bed with Storage",
        size_sqm: 22,
        amenities: "Own Bathroom with Bidet & Shower, Kitchen Sink, Inverter Aircon, Wardrobe Cabinet, Submetered Power/Water",
        status: "Available",
        total_booked: 180,
        image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "pdp-transient-weekly",
        room_number: "Apt 204",
        room_name: "Short-Term Weekly Studio for Reviewees/Expats",
        room_type: "Weekly Transient",
        price: 4500,
        rate_type: "weekly",
        duration: "per week",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 20,
        amenities: "All utilities included, Wi-Fi, Fresh beddings weekly, Rice cooker & electric kettle",
        status: "Available",
        total_booked: 110,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "studio",
          name: "Main Studio Unit",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Compact, well-laid out studio with double bed, wardrobe, and dining nook",
          hotspots: [
            { targetRoomId: "kitchen", label: "To Kitchenette Area", yaw: 90, pitch: -5 },
            { targetRoomId: "bath", label: "To Private Bathroom", yaw: 170, pitch: -4 }
          ]
        },
        {
          id: "kitchen",
          name: "Kitchenette Counter",
          pano: "https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=2000&q=80",
          description: "Equipped with stainless sink, overhead cabinets, induction stove space, and fridge spot",
          hotspots: [{ targetRoomId: "studio", label: "Back to Living Area", yaw: 270, pitch: -4 }]
        },
        {
          id: "bath",
          name: "Private Ceramic Bathroom",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Tiled private shower with bidet toilet and mirror cabinet",
          hotspots: [{ targetRoomId: "studio", label: "Back to Studio", yaw: 0, pitch: -4 }]
        }
      ]
    }
  },
  {
    id: "rental-taft-bedspace",
    title: "Taft Avenue Student & Executive Bedspace",
    branch_name: "Near DLSU & LRT Vito Cruz",
    brand: "Taft Haven Dorms",
    category: "rental",
    categoryDisplay: "Bedspace Dormitory",
    address: "Taft Avenue cor. Estrada St, Malate, Manila",
    city: "Historic Manila",
    rating: 4.5,
    ratingCount: 520,
    viewsCount: 2840,
    totalBooked: 610,
    price: 4200,
    price_from: 4200,
    durationLabel: "month/bed",
    rateTypeLabel: "MONTHLY BEDSPACE",
    rateType: "monthly",
    lat: 14.5645,
    lng: 120.9944,
    image: "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80",
    tags: ["Capsule Pod Bedspace", "Airconditioned", "Study Lounge", "Free High-Speed Wi-Fi", "Keycard Lock"],
    statusText: "Beds Available Now",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "taft-pod-bed",
        room_number: "Pod 4-B",
        room_name: "Private Capsule Bedspace Pod (Monthly)",
        room_type: "Capsule Bedspace",
        price: 4200,
        rate_type: "monthly",
        duration: "per month",
        capacity: "1 Person",
        bed_type: "Single Capsule with Privacy Blind",
        size_sqm: 6,
        amenities: "Privacy blackout roller blind, Reading light, Universal socket & USB port, Personal locker with padlock, Air-conditioned 24/7",
        status: "Available",
        total_booked: 430,
        image: "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "taft-private-solo",
        room_number: "Room 12",
        room_name: "Solo Private Room for Reviewees (Monthly)",
        room_type: "Solo Student Room",
        price: 8500,
        rate_type: "monthly",
        duration: "per month",
        capacity: "1 Person",
        bed_type: "Single Bed",
        size_sqm: 12,
        amenities: "Personal study table, Air conditioning, Wardrobe, Shared kitchen & microwave",
        status: "Available",
        total_booked: 180,
        image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "podroom",
          name: "Capsule Dorm Room",
          pano: "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=2000&q=80",
          description: "Clean, air-conditioned capsule pods with individual privacy blinds and lockboxes",
          hotspots: [{ targetRoomId: "study", label: "To Quiet Study Lounge", yaw: 80, pitch: -3 }]
        },
        {
          id: "study",
          name: "Study & Co-Working Lounge",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Bright study tables with electrical plugs and 150Mbps fiber connection",
          hotspots: [{ targetRoomId: "podroom", label: "Back to Dorm Pods", yaw: 260, pitch: -4 }]
        }
      ]
    }
  },
  {
    id: "rental-katipunan-loft",
    title: "Katipunan Student Loft & Bedspace",
    branch_name: "Loyola Heights QC",
    brand: "Katipunan Dorms",
    category: "rental",
    categoryDisplay: "Student Apartment",
    address: "Katipunan Ave cor. Esteban Abada, Quezon City",
    city: "Quezon City",
    rating: 4.7,
    ratingCount: 440,
    viewsCount: 2190,
    totalBooked: 520,
    price: 5500,
    price_from: 5500,
    durationLabel: "month/bed",
    rateTypeLabel: "MONTHLY STUDENT BED",
    rateType: "monthly",
    lat: 14.6394,
    lng: 121.0751,
    image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80",
    tags: ["Opposite Ateneo & Miriam", "Study Hall", "Biometric Gate", "High-Speed Mesh Wi-Fi"],
    statusText: "Semester Inquiries Open",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "katipunan-bed-shared",
        room_number: "Loft 201-B",
        room_name: "Upper Bunk Bedspace in 4-Person Loft",
        room_type: "Shared Loft",
        price: 5500,
        rate_type: "monthly",
        duration: "per month",
        capacity: "1 Student",
        bed_type: "Single Bunk Bed",
        size_sqm: 8,
        amenities: "Study desk underneath bunk, Wardrobe locker, Aircon, In-room bathroom with water heater",
        status: "Available",
        total_booked: 340,
        image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "katipunan-studio-solo",
        room_number: "Studio 504",
        room_name: "Solo Student Studio Flat (Monthly)",
        room_type: "Studio Flat",
        price: 13500,
        rate_type: "monthly",
        duration: "per month",
        capacity: "1-2 Students",
        bed_type: "Double Bed",
        size_sqm: 21,
        amenities: "Private Kitchen, Personal Refrigerator, Study Desk, Aircon, Balcony",
        status: "Available",
        total_booked: 180,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "loft",
          name: "Loft Living & Study Space",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "High-ceiling loft with wooden study desks and ergonomic chairs",
          hotspots: [{ targetRoomId: "bunks", label: "To Sleeping Bunks", yaw: 90, pitch: 12 }]
        },
        {
          id: "bunks",
          name: "Cozy Loft Bunks",
          pano: "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=2000&q=80",
          description: "Elevated wooden bunk beds with reading lights",
          hotspots: [{ targetRoomId: "loft", label: "Down to Study Area", yaw: 270, pitch: -10 }]
        }
      ]
    }
  },
  {
    id: "rental-bgc-south-flat",
    title: "BGC South Transient Studio",
    branch_name: "Market! Market! Vicinity",
    brand: "UrbanFlats Manila",
    category: "rental",
    categoryDisplay: "Short-Term Studio",
    address: "Sampaguita St cor. 26th St, Pembo, Taguig City",
    city: "BGC Taguig",
    rating: 4.6,
    ratingCount: 380,
    viewsCount: 1980,
    totalBooked: 390,
    price: 15500,
    price_from: 15500,
    durationLabel: "month",
    rateTypeLabel: "MONTHLY APARTMENT",
    rateType: "monthly",
    lat: 14.5461,
    lng: 121.0582,
    image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80",
    tags: ["5 Mins to BGC", "Affordable BGC Living", "Airconditioned", "Kitchenette"],
    statusText: "Units Ready for Move-In",
    statusBadgeClass: "status-walkin",
    instant: true,
    rooms: [
      {
        id: "bgc-south-studio",
        room_number: "Unit 301",
        room_name: "Modern Furnished Studio near BGC High Street",
        room_type: "Monthly Studio",
        price: 15500,
        rate_type: "monthly",
        duration: "per month",
        capacity: "2 Tenants",
        bed_type: "Double Bed",
        size_sqm: 23,
        amenities: "Air conditioning, Private bath, Induction stove, Dining nook, High-speed fiber",
        status: "Available",
        total_booked: 260,
        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80"
      },
      {
        id: "bgc-south-weekly",
        room_number: "Unit 202",
        room_name: "Weekly Transient Studio for BGC Contractors",
        room_type: "Weekly Transient",
        price: 4900,
        rate_type: "weekly",
        duration: "per week",
        capacity: "2 Guests",
        bed_type: "Double Bed",
        size_sqm: 21,
        amenities: "Furnished with linens, Hot shower, Wi-Fi, Water & power inclusive",
        status: "Available",
        total_booked: 130,
        image: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80"
      }
    ],
    showroom: {
      rooms: [
        {
          id: "room",
          name: "Modern Studio",
          pano: "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=2000&q=80",
          description: "Clean, stylish living and sleeping space just a short walk to BGC High Street",
          hotspots: [{ targetRoomId: "bath", label: "To Bathroom", yaw: 85, pitch: -5 }]
        },
        {
          id: "bath",
          name: "Private Bath",
          pano: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=2000&q=80",
          description: "Modern ceramic bath with water heater",
          hotspots: [{ targetRoomId: "room", label: "Back to Studio", yaw: 265, pitch: -4 }]
        }
      ]
    }
  }
];

const dest = path.join(__dirname, 'data', 'stays_inventory.json');
fs.writeFileSync(dest, JSON.stringify(stays, null, 2), 'utf-8');
console.log('Successfully wrote', stays.length, 'stays to', dest);
