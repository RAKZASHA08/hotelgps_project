<?php
// app/Models/Stay.php

namespace App\Models;

class Stay {
    public string $id;
    public string $title;
    public string $branchName;
    public string $brand;
    public string $category;
    public string $categoryDisplay;
    public string $address;
    public string $city;
    public float $rating;
    public int $ratingCount;
    public int $viewsCount;
    public int $totalBooked;
    public float $price;
    public float $priceFrom;
    public string $durationLabel;
    public float $lat;
    public float $lng;
    public string $image;
    public array $tags;
    public string $statusText;
    public string $statusBadgeClass;
    public bool $instant;
    public string $nearestTerminal;
    public string $transitType;
    public string $walkTime;
    public array $hourlyRates;
    public array $rooms;
    public ?array $showroom;

    public function __construct(array $data) {
        $this->id               = $data['id'] ?? '';
        $this->title            = $data['title'] ?? '';
        $this->branchName       = $data['branch_name'] ?? 'Main Branch';
        $this->brand            = $data['brand'] ?? 'Motel / Hotel';
        $this->category         = $data['category'] ?? 'motel';
        $this->categoryDisplay  = $data['categoryDisplay'] ?? $data['category_display'] ?? 'Motel / Drive-in';
        $this->address          = $data['address'] ?? '';
        $this->city             = $data['city'] ?? 'Metro Manila';
        $this->rating           = (float)($data['rating'] ?? 4.5);
        $this->ratingCount      = (int)($data['ratingCount'] ?? $data['rating_count'] ?? 0);
        $this->viewsCount       = (int)($data['viewsCount'] ?? $data['views_count'] ?? 0);
        $this->totalBooked      = (int)($data['totalBooked'] ?? $data['total_booked'] ?? 0);
        $this->price            = (float)($data['price'] ?? $data['price_from'] ?? 450);
        $this->priceFrom        = (float)($data['price_from'] ?? $this->price);
        $this->durationLabel    = $data['durationLabel'] ?? $data['duration_label'] ?? '3-Hour Wash Rate';
        $this->lat              = (float)($data['lat'] ?? 14.5995);
        $this->lng              = (float)($data['lng'] ?? 120.9842);
        $this->image            = $data['image'] ?? '';
        $this->tags             = $data['tags'] ?? ['Walk-in Ready', 'Near Transit'];
        $this->statusText       = $data['statusText'] ?? $data['status_text'] ?? 'Rooms Available Now';
        $this->statusBadgeClass = $data['statusBadgeClass'] ?? $data['status_badge'] ?? 'status-walkin';
        $this->instant          = (bool)($data['instant'] ?? true);
        $this->nearestTerminal  = $data['nearest_terminal'] ?? 'Nearest Station / Terminal';
        $this->transitType      = $data['transit_type'] ?? 'MRT / Busway';
        $this->walkTime         = $data['walk_time'] ?? '3 mins walk';
        $this->hourlyRates      = $data['hourly_rates'] ?? [
            '2hr'  => 360,
            '3hr'  => 450,
            '6hr'  => 750,
            '12hr' => 1100,
            '24hr' => 1600
        ];

        // Enrich rooms with image_360 if not present
        $rawRooms = $data['rooms'] ?? [];
        $enrichedRooms = [];
        foreach ($rawRooms as $rm) {
            if (empty($rm['image_360'])) {
                $rName = strtolower($rm['room_name'] ?? '');
                if (str_contains($rName, 'suite') || str_contains($rName, 'executive') || str_contains($rName, 'luxury') || str_contains($rName, 'presidential')) {
                    $rm['image_360'] = 'img/luxury_suite.jpg';
                } elseif (str_contains($rName, 'condo') || str_contains($rName, 'studio') || str_contains($rName, 'loft') || str_contains($rName, 'balcony')) {
                    $rm['image_360'] = 'img/skyline_condo.jpg';
                } elseif (str_contains($rName, 'transit') || str_contains($rName, 'capsule') || str_contains($rName, 'cabin') || str_contains($rName, 'wash')) {
                    $rm['image_360'] = 'img/transit_lounge.jpg';
                } else {
                    $rm['image_360'] = 'img/pano_bedroom.jpg';
                }
            }
            $enrichedRooms[] = $rm;
        }
        $this->rooms = $enrichedRooms;

        // Ensure showroom exists
        $this->showroom = !empty($data['showroom']['rooms']) ? $data['showroom'] : self::buildDefaultShowroom($this->category, $this->title);
    }

    public static function buildDefaultShowroom(string $category, string $title = ''): array {
        $cat = strtolower($category);
        if ($cat === 'motel' || $cat === 'sogo') {
            return [
                'initial_room' => 'bedroom',
                'rooms' => [
                    'bedroom' => [
                        'title' => 'Suite Bedroom',
                        'image_360' => 'img/pano_bedroom.jpg',
                        'hotspots' => [
                            ['x' => 74, 'y' => 52, 'label' => 'Private Shower', 'target' => 'bathroom'],
                            ['x' => 26, 'y' => 58, 'label' => 'Drive-In Garage', 'target' => 'garage']
                        ]
                    ],
                    'bathroom' => [
                        'title' => 'Private Shower & Jacuzzi',
                        'image_360' => 'img/pano_bathroom.jpg',
                        'hotspots' => [
                            ['x' => 22, 'y' => 55, 'label' => 'Back to Suite', 'target' => 'bedroom']
                        ]
                    ],
                    'garage' => [
                        'title' => 'Private Drive-In Garage',
                        'image_360' => 'img/transit_lounge.jpg',
                        'hotspots' => [
                            ['x' => 60, 'y' => 48, 'label' => 'Enter Suite', 'target' => 'bedroom']
                        ]
                    ]
                ]
            ];
        } elseif ($cat === 'airbnb' || $cat === 'condo') {
            return [
                'initial_room' => 'living',
                'rooms' => [
                    'living' => [
                        'title' => 'Living & Dining Suite',
                        'image_360' => 'img/skyline_condo.jpg',
                        'hotspots' => [
                            ['x' => 70, 'y' => 50, 'label' => 'Master Bedroom', 'target' => 'bedroom'],
                            ['x' => 28, 'y' => 54, 'label' => 'City View Balcony', 'target' => 'balcony']
                        ]
                    ],
                    'bedroom' => [
                        'title' => 'Master Bedroom',
                        'image_360' => 'img/pano_bedroom.jpg',
                        'hotspots' => [
                            ['x' => 20, 'y' => 55, 'label' => 'Back to Living', 'target' => 'living'],
                            ['x' => 78, 'y' => 52, 'label' => 'Ensuite Bath', 'target' => 'bathroom']
                        ]
                    ],
                    'bathroom' => [
                        'title' => 'Ensuite Luxury Bath',
                        'image_360' => 'img/pano_bathroom.jpg',
                        'hotspots' => [
                            ['x' => 18, 'y' => 58, 'label' => 'Back to Bedroom', 'target' => 'bedroom']
                        ]
                    ],
                    'balcony' => [
                        'title' => 'City Skyline Balcony',
                        'image_360' => 'img/luxury_suite.jpg',
                        'hotspots' => [
                            ['x' => 30, 'y' => 56, 'label' => 'Back Inside', 'target' => 'living']
                        ]
                    ]
                ]
            ];
        } elseif ($cat === 'hotel' || $cat === 'resort' || $cat === 'heritage') {
            return [
                'initial_room' => 'suite',
                'rooms' => [
                    'suite' => [
                        'title' => 'Executive Grand Suite',
                        'image_360' => 'img/luxury_suite.jpg',
                        'hotspots' => [
                            ['x' => 74, 'y' => 52, 'label' => 'Marble Bathroom', 'target' => 'bathroom'],
                            ['x' => 26, 'y' => 54, 'label' => 'Club Lounge & Bar', 'target' => 'lounge']
                        ]
                    ],
                    'bathroom' => [
                        'title' => 'Marble Spa Bathroom',
                        'image_360' => 'img/pano_bathroom.jpg',
                        'hotspots' => [
                            ['x' => 20, 'y' => 55, 'label' => 'Return to Suite', 'target' => 'suite']
                        ]
                    ],
                    'lounge' => [
                        'title' => 'Executive Club Lounge',
                        'image_360' => 'img/transit_lounge.jpg',
                        'hotspots' => [
                            ['x' => 65, 'y' => 50, 'label' => 'Enter Grand Suite', 'target' => 'suite']
                        ]
                    ]
                ]
            ];
        } else {
            return [
                'initial_room' => 'living',
                'rooms' => [
                    'living' => [
                        'title' => 'Living Area',
                        'image_360' => 'img/skyline_condo.jpg',
                        'hotspots' => [
                            ['x' => 70, 'y' => 52, 'label' => 'Bedroom', 'target' => 'bedroom'],
                            ['x' => 28, 'y' => 55, 'label' => 'Kitchenette', 'target' => 'kitchen']
                        ]
                    ],
                    'bedroom' => [
                        'title' => 'Master Bedroom',
                        'image_360' => 'img/pano_bedroom.jpg',
                        'hotspots' => [
                            ['x' => 22, 'y' => 56, 'label' => 'Back to Living', 'target' => 'living']
                        ]
                    ],
                    'kitchen' => [
                        'title' => 'Kitchen & Dining',
                        'image_360' => 'img/pano_bathroom.jpg',
                        'hotspots' => [
                            ['x' => 65, 'y' => 50, 'label' => 'Living Area', 'target' => 'living']
                        ]
                    ]
                ]
            ];
        }
    }

    public function toArray(): array {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'branch_name'        => $this->branchName,
            'brand'              => $this->brand,
            'category'           => $this->category,
            'categoryDisplay'    => $this->categoryDisplay,
            'address'            => $this->address,
            'city'               => $this->city,
            'rating'             => $this->rating,
            'ratingCount'        => $this->ratingCount,
            'viewsCount'         => $this->viewsCount,
            'totalBooked'        => $this->totalBooked,
            'price'              => $this->price,
            'price_from'         => $this->priceFrom,
            'durationLabel'      => $this->durationLabel,
            'rateTypeLabel'      => ($this->category === 'motel' || $this->category === 'sogo') ? 'HOURLY WALK-IN RATE' : 'NIGHTLY RATE',
            'lat'                => $this->lat,
            'lng'                => $this->lng,
            'image'              => $this->image,
            'tags'               => $this->tags,
            'statusText'         => $this->statusText,
            'statusBadgeClass'   => $this->statusBadgeClass,
            'instant'            => $this->instant,
            'nearest_terminal'   => $this->nearestTerminal,
            'transit_type'       => $this->transitType,
            'walk_time'          => $this->walkTime,
            'hourly_rates'       => $this->hourlyRates,
            'rooms'              => $this->rooms,
            'showroom'           => $this->showroom
        ];
    }
}
