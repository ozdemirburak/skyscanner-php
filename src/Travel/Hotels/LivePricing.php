<?php

namespace OzdemirBurak\SkyScanner\Travel\Hotels;

use OzdemirBurak\SkyScanner\TravelService;
use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class LivePricing extends TravelService
{
    use ImageTrait;

    /**
     * Main data property that contains pricing information
     *
     * @var string
     */
    protected $property = 'results';

    /**
     * API Endpoint
     *
     * @var string
     */
    protected $endpoint = 'prices/search/entity/{entity_id}';

    /**
     * API Uri
     *
     * @var string
     */
    protected $uri = '?apikey={apiKey}&market={country}&currency={currency}&locale={locale}&checkin_date={checkin_date}&checkout_date={checkout_date}&adults={adults}&rooms={rooms}&images={images}&image_resolution={image_resolution}&image_type={image_type}&boost_official_partners={boost_official_partners}&sort={sort}&limit={limit}&offset={offset}&partners_per_hotel={partners_per_hotel}&enhanced={enhanced}';

    /**
     * API URL
     *
     * @var string
     */
    protected $url = 'https://gateway.skyscanner.net/hotels/v1/';

    /**
     * Entity to search for hotels prices in it
     * For instance, 27544008 is London
     *
     * @var int
     */
    protected $entity_id;

    /**
     * Indicates which is the device related to the client.
     *
     * T for tablet
     * D for desktop
     * M for mobile
     * N if you are not able to detect the device type
     *
     * @var string
     */
    protected $userAgent = 'N';

    /**
     * Check-in date in YYYY-MM-DD format
     *
     * @var string
     */
    protected $checkin_date;

    /**
     * Check-out date in YYYY-MM-DD format
     *
     * @var string
     */
    protected $checkout_date;

    /**
     * Number of rooms
     *
     * @var int
     */
    protected $rooms = 1;

    /**
     * Number of adults
     *
     * @var int
     */
    protected $adults = 2;

    /**
     * Maximum number of images to retrieve per each hotel between 1-30, default: 3
     *
     * @var int
     */
    protected $images;

    /**
     * Resolution options, high or low, default: high
     *
     * @var string
     */
    protected $image_resolution;

    /**
     * The format of the images
     *
     * @var string
     */
    protected $image_type;

    /**
     * Indicates whether prices from official partners must be shown in the first place [1] or not [0]
     *
     * @var int
     */
    protected $boost_official_partners;

    /**
     * Sort by a given attribute. By default the relevance sorting is applied,
     * relevance, -relevance, price, -price, distance, -distance, rating, -rating, stars, -stars
     *
     * @var string
     */
    protected $sort;

    /**
     * Filter
     * Return only hotels where the cheaper price is at least price_min (included).
     * Cannot be used together with price_buckets
     *
     * @var int
     */
    protected $price_min;

    /**
     * Filter
     * Return only hotels where the cheaper price is at most price_max (included).
     * Cannot be used together with price_buckets
     *
     * @var int
     */
    protected $price_max;

    /**
     * OR filter
     * Return only hotels with offers inside the specified buckets.
     * Cannot be used together with price_min/price_max
     *
     * @var array
     */
    protected $price_buckets;

    /**
     * OR filter
     * Return only results where a district matches
     *
     * @var string
     */
    protected $district;

    /**
     * OR filter
     * Return only results where a star category matches.
     * The values must be TravelAPI ids
     *
     * @var
     */
    protected $stars;

    /**
     * OR filter
     * When the search is done for an entity that contains different cities, this filter is available.
     * Returns only results where the cities match.
     * The values must be TravelAPI entity ids
     *
     * @var
     */
    protected $city;

    /**
     * OR filter
     * Return only results where a hotel chains matches.
     * The values must be TravelAPI ids
     *
     * @var
     */
    protected $chain;

    /**
     * AND filter
     * Return only results where all amenities match.
     * The values must be TravelAPI ids
     *
     * @var
     */
    protected $amenities;

    /**
     * OR filter
     * Return only results where a cancellation policies matches.
     * Options are: free_cancellation, non_refundable, refundable, special_conditions
     *
     * @var string
     */
    protected $cancellation;

    /**
     * OR filter
     * Return only results where a meal plan matches.
     * Options are: room_only, breakfast_included, half_board, full_board, all_inclusive
     *
     * @var string
     */
    protected $meal_plan;

    /**
     * OR filter
     * Return only results where an accommodation type matches.
     * The values must be TravelAPI ids
     *
     * @var
     */
    protected $property_type;

    /**
     * Filter
     * Return only results where hotel name matches
     *
     * @var string
     */
    protected $hotel_name;

    /**
     * Number of results to retrieve between 1-30
     * default: 30
     *
     * @var int
     */
    protected $limit;

    /**
     * How many results to skip from the first position, useful for paginating
     * default: 0
     *
     * @var int
     */
    protected $offset;

    /**
     * Maximum numbers of partners to retrieve per each hotel.
     * Note that 0 means all the available partners
     * default: 3
     *
     * @var int
     */
    protected $partners_per_hotel;

    /**
     * This parameter allows you to add additional content to the default response.
     *
     * Available options are:
     *
     * filters: Returns extra object in the response including the filters like stars, district, city, etc.
     * price_slider: Return the price_slider.
     * partners: Returns information about the active partners in the system. is_official, the logo,
     *  the name and the website_id.
     * images: Returns images for the hotels. With the partner website_id and the urls.
     * amenities: Returns the hotels amenities.
     * query_location: Returns the location (higher level entities according to the searched entity) and
     *  map boundary (the coordinates of the search area).
     * extras: Returns the hotel chain of the hotels.
     * translations: Returns a dictionary with all literals and their corresponding translations using the
     *  request locale.
     * detailed_reviews: Returns information from the hotel reviews provided by Trustyou
     *
     * @var string;
     */
    protected $enhanced;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return str_replace('{entity_id}', $this->entity_id, $this->url . $this->endpoint);
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHotels()
    {
        if ($this->init()) {
            return $this->data->{$this->property};
        }
        $this->printErrorMessage($this->getResponseMessage());
        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultParameters(): array
    {
        return array_merge(parent::getDefaultParameters(), [
            'x-user-agent' => $this->userAgent . ';B2B'
        ]);
    }
}
