/**
 * rebuild_inventory.js
 * Rewrites data/stays_inventory.json with correct showroom schema
 * Showroom rooms → keyed object, hotspot positions as x/y percentages
 */
const fs = require('fs');
const path = require('path');

// Realistic panoramic images for room types (wide/landscape Unsplash images)
const PANO_IMAGES = {
  bedroom:  'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=2400&q=85',
  bedroom2: 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=2400&q=85',
  bathroom: 'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?auto=format&fit=crop&w=2400&q=85',
  living:   'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=2400&q=85',
  garage:   'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=2400&q=85',
  balcony:  'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=2400&q=85',
  kitchen:  'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=2400&q=85',
  lounge:   'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=2400&q=85',
};

// Build a standard showroom for motel type
function motelShowroom() {
  return {
    initial_room: 'bedroom',
    rooms: {
      bedroom: {
        title: 'Suite Bedroom',
        image_360: PANO_IMAGES.bedroom,
        hotspots: [
          { x: 72, y: 52, label: 'To Bathroom', target: 'bathroom' },
          { x: 25, y: 60, label: 'Garage Entry', target: 'garage' }
        ]
      },
      bathroom: {
        title: 'Private Shower',
        image_360: PANO_IMAGES.bathroom,
        hotspots: [
          { x: 20, y: 55, label: 'Back to Suite', target: 'bedroom' }
        ]
      },
      garage: {
        title: 'Drive-In Garage',
        image_360: PANO_IMAGES.garage,
        hotspots: [
          { x: 60, y: 45, label: 'Into Suite', target: 'bedroom' }
        ]
      }
    }
  };
}

// Build a standard showroom for airbnb/condo type
function airbnbShowroom() {
  return {
    initial_room: 'living',
    rooms: {
      living: {
        title: 'Living Area',
        image_360: PANO_IMAGES.living,
        hotspots: [
          { x: 68, y: 50, label: 'Bedroom', target: 'bedroom' },
          { x: 30, y: 55, label: 'Balcony / View', target: 'balcony' }
        ]
      },
      bedroom: {
        title: 'Master Bedroom',
        image_360: PANO_IMAGES.bedroom2,
        hotspots: [
          { x: 20, y: 55, label: 'Back to Living', target: 'living' },
          { x: 75, y: 52, label: 'Ensuite Bath', target: 'bathroom' }
        ]
      },
      bathroom: {
        title: 'Ensuite Bathroom',
        image_360: PANO_IMAGES.bathroom,
        hotspots: [
          { x: 18, y: 58, label: 'Back to Bedroom', target: 'bedroom' }
        ]
      },
      balcony: {
        title: 'City View Balcony',
        image_360: PANO_IMAGES.balcony,
        hotspots: [
          { x: 25, y: 60, label: 'Back Inside', target: 'living' }
        ]
      }
    }
  };
}

// Build a standard showroom for rental/apartment type
function rentalShowroom() {
  return {
    initial_room: 'living',
    rooms: {
      living: {
        title: 'Living Room',
        image_360: PANO_IMAGES.lounge,
        hotspots: [
          { x: 70, y: 52, label: 'Bedroom', target: 'bedroom' },
          { x: 28, y: 55, label: 'Kitchen', target: 'kitchen' }
        ]
      },
      bedroom: {
        title: 'Bedroom',
        image_360: PANO_IMAGES.bedroom2,
        hotspots: [
          { x: 22, y: 56, label: 'Back to Living', target: 'living' }
        ]
      },
      kitchen: {
        title: 'Kitchen & Dining',
        image_360: PANO_IMAGES.kitchen,
        hotspots: [
          { x: 65, y: 50, label: 'Living Room', target: 'living' }
        ]
      }
    }
  };
}

// Read existing inventory
const inventoryPath = path.join(__dirname, 'data', 'stays_inventory.json');
const stays = JSON.parse(fs.readFileSync(inventoryPath, 'utf8'));

// Rebuild showrooms
const rebuilt = stays.map(stay => {
  let showroom;
  if (stay.category === 'motel') showroom = motelShowroom();
  else if (stay.category === 'airbnb') showroom = airbnbShowroom();
  else showroom = rentalShowroom();

  return { ...stay, showroom };
});

fs.writeFileSync(inventoryPath, JSON.stringify(rebuilt, null, 2), 'utf8');
console.log(`✓ Rebuilt ${rebuilt.length} stays with proper showroom schemas.`);
console.log('  Categories:', rebuilt.reduce((a,s) => { a[s.category]=(a[s.category]||0)+1; return a; }, {}));

// Verify first stay
const s0 = rebuilt[0];
console.log('\n✓ Sample showroom for', s0.title, ':');
Object.entries(s0.showroom.rooms).forEach(([k, r]) => {
  console.log(`  [${k}] "${r.title}" | image_360: ${r.image_360 ? 'OK' : 'MISSING'} | hotspots: ${r.hotspots.length}`);
});
console.log('  initial_room:', s0.showroom.initial_room);
