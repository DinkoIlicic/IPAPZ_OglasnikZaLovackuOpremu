<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:57 AM
 */

namespace App\Controller;

use App\Entity\Shipping;
use App\Form\ShippingDefaultFormType;
use App\Form\ShippingFormType;
use App\Repository\ShippingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShippingController extends AbstractController
{
    /**
     * @Route("/admin/show-shipping-price", name="show_shipping_price")
     * @param             ShippingRepository $shippingRepository
     * @param             EntityManagerInterface $entityManager
     * @param             Request $request
     * @return            \Symfony\Component\HttpFoundation\Response
     */
    public function showShippingPrice(
        ShippingRepository $shippingRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $default = $shippingRepository->findOneBy(['country' => 'Default']);
        $defaultForm = $this->createForm(ShippingDefaultFormType::class, $default);
        $defaultForm->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $defaultForm->isSubmitted() && $defaultForm->isValid()) {
            $default = $defaultForm->getData();
            $entityManager->merge($default);
            $entityManager->flush();
        }

        $form = $this->createForm(ShippingFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $countryName = $form->get('query')->getData();
            $countryPrice = $form->get('price')->getData();
            /**
             * @var Shipping $shipping
             */
            $shipping = $shippingRepository->findOneBy(['country' => $countryName]);
            if ($shipping instanceof Shipping) {
                $shipping->setPrice($countryPrice);
                $entityManager->merge($shipping);
                $entityManager->flush();
            }
        }

        return $this->render(
            '/admin/view_shipping_price.html.twig',
            [
                'default' => $default,
                'defaultForm' => $defaultForm->createView(),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/handle-search-shipping-price/{_query?}",
     *      name="handle_search_shipping_price", methods={"POST", "GET"})
     * @var                                     $_query
     * @return                                  JsonResponse
     */
    public function handleSearchRequestShipping($_query)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository(Shipping::class)->findByName($_query);
        $jsonObject = $this->returnJsonObjectShipping($data);
        return new JsonResponse($jsonObject, 200, [], true);
    }

    /**
     * @var                                     $data
     * @return                                  \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function returnJsonObjectShipping($data)
    {
        // setting up the serializer
        $normalizers = [
            new ObjectNormalizer()
        ];
        $encoders = [
            new JsonEncoder()
        ];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize(
            $data,
            'json',
            [
                'circular_reference_handler' => function ($shipping) {
                    /**
                     * @var $shipping \App\Entity\Shipping
                     */
                    return $shipping->getCountry();
                }
            ]
        );
        return $jsonObject;
    }

    /**
     * @Route("/admin/ajax-shipping/{country?}", name="ajax_shipping")
     * @param                                  ShippingRepository $shippingRepository
     * @param                                  Shipping $country
     * @return                                 JsonResponse
     */
    public function ajaxListShipping(
        ShippingRepository $shippingRepository,
        Shipping $country = null
    ) {
        $shipping = $shippingRepository->findCountry($country->getCountry());

        return new JsonResponse(
            [
                'shipping' => $shipping
            ]
        );
    }

    private function bulkAllCountries(EntityManagerInterface $entityManager)
    {
        $arrayCountries =
            [
                ['countryCode' => 'AF', 'Country' => 'Afghanistan'],
                ['countryCode' => 'AX', 'Country' => 'Åland Islands'],
                ['countryCode' => 'AL', 'Country' => 'Albania'],
                ['countryCode' => 'DZ', 'Country' => 'Algeria'],
                ['countryCode' => 'AS', 'Country' => 'American Samoa'],
                ['countryCode' => 'AD', 'Country' => 'Andorra'],
                ['countryCode' => 'AO', 'Country' => 'Angola'],
                ['countryCode' => 'AI', 'Country' => 'Anguilla'],
                ['countryCode' => 'AQ', 'Country' => 'Antarctica'],
                ['countryCode' => 'AG', 'Country' => 'Antigua & Barbuda'],
                ['countryCode' => 'AR', 'Country' => 'Argentina'],
                ['countryCode' => 'AM', 'Country' => 'Armenia'],
                ['countryCode' => 'AW', 'Country' => 'Aruba'],
                ['countryCode' => 'AC', 'Country' => 'Ascension Island'],
                ['countryCode' => 'AU', 'Country' => 'Australia'],
                ['countryCode' => 'AT', 'Country' => 'Austria'],
                ['countryCode' => 'AZ', 'Country' => 'Azerbaijan'],
                ['countryCode' => 'BS', 'Country' => 'Bahamas'],
                ['countryCode' => 'BH', 'Country' => 'Bahrain'],
                ['countryCode' => 'BD', 'Country' => 'Bangladesh'],
                ['countryCode' => 'BB', 'Country' => 'Barbados'],
                ['countryCode' => 'BY', 'Country' => 'Belarus'],
                ['countryCode' => 'BE', 'Country' => 'Belgium'],
                ['countryCode' => 'BZ', 'Country' => 'Belize'],
                ['countryCode' => 'BJ', 'Country' => 'Benin'],
                ['countryCode' => 'BM', 'Country' => 'Bermuda'],
                ['countryCode' => 'BT', 'Country' => 'Bhutan'],
                ['countryCode' => 'BO', 'Country' => 'Bolivia'],
                ['countryCode' => 'BA', 'Country' => 'Bosnia & Herzegovina'],
                ['countryCode' => 'BW', 'Country' => 'Botswana'],
                ['countryCode' => 'BR', 'Country' => 'Brazil'],
                ['countryCode' => 'IO', 'Country' => 'British Indian Ocean Territory'],
                ['countryCode' => 'VG', 'Country' => 'British Virgin Islands'],
                ['countryCode' => 'BN', 'Country' => 'Brunei'],
                ['countryCode' => 'BG', 'Country' => 'Bulgaria'],
                ['countryCode' => 'BF', 'Country' => 'Burkina Faso'],
                ['countryCode' => 'BI', 'Country' => 'Burundi'],
                ['countryCode' => 'KH', 'Country' => 'Cambodia'],
                ['countryCode' => 'CM', 'Country' => 'Cameroon'],
                ['countryCode' => 'CA', 'Country' => 'Canada'],
                ['countryCode' => 'IC', 'Country' => 'Canary Islands'],
                ['countryCode' => 'CV', 'Country' => 'Cape Verde'],
                ['countryCode' => 'BQ', 'Country' => 'Caribbean Netherlands'],
                ['countryCode' => 'KY', 'Country' => 'Cayman Islands'],
                ['countryCode' => 'CF', 'Country' => 'Central African Republic'],
                ['countryCode' => 'EA', 'Country' => 'Ceuta & Melilla'],
                ['countryCode' => 'TD', 'Country' => 'Chad'],
                ['countryCode' => 'CL', 'Country' => 'Chile'],
                ['countryCode' => 'CN', 'Country' => 'China'],
                ['countryCode' => 'CX', 'Country' => 'Christmas Island'],
                ['countryCode' => 'CC', 'Country' => 'Cocos (Keeling) Islands'],
                ['countryCode' => 'CO', 'Country' => 'Colombia'],
                ['countryCode' => 'KM', 'Country' => 'Comoros'],
                ['countryCode' => 'CG', 'Country' => 'Congo - Brazzaville'],
                ['countryCode' => 'CD', 'Country' => 'Congo - Kinshasa'],
                ['countryCode' => 'CK', 'Country' => 'Cook Islands'],
                ['countryCode' => 'CR', 'Country' => 'Costa Rica'],
                ['countryCode' => 'CI', 'Country' => 'Côte d’Ivoire'],
                ['countryCode' => 'HR', 'Country' => 'Croatia'],
                ['countryCode' => 'CU', 'Country' => 'Cuba'],
                ['countryCode' => 'CW', 'Country' => 'Curaçao'],
                ['countryCode' => 'CY', 'Country' => 'Cyprus'],
                ['countryCode' => 'CZ', 'Country' => 'Czechia'],
                ['countryCode' => 'DK', 'Country' => 'Denmark'],
                ['countryCode' => 'DG', 'Country' => 'Diego Garcia'],
                ['countryCode' => 'DJ', 'Country' => 'Djibouti'],
                ['countryCode' => 'DM', 'Country' => 'Dominica'],
                ['countryCode' => 'DO', 'Country' => 'Dominican Republic'],
                ['countryCode' => 'EC', 'Country' => 'Ecuador'],
                ['countryCode' => 'EG', 'Country' => 'Egypt'],
                ['countryCode' => 'SV', 'Country' => 'El Salvador'],
                ['countryCode' => 'GQ', 'Country' => 'Equatorial Guinea'],
                ['countryCode' => 'ER', 'Country' => 'Eritrea'],
                ['countryCode' => 'EE', 'Country' => 'Estonia'],
                ['countryCode' => 'ET', 'Country' => 'Ethiopia'],
                ['countryCode' => 'EZ', 'Country' => 'Eurozone'],
                ['countryCode' => 'FK', 'Country' => 'Falkland Islands'],
                ['countryCode' => 'FO', 'Country' => 'Faroe Islands'],
                ['countryCode' => 'FJ', 'Country' => 'Fiji'],
                ['countryCode' => 'FI', 'Country' => 'Finland'],
                ['countryCode' => 'FR', 'Country' => 'France'],
                ['countryCode' => 'GF', 'Country' => 'French Guiana'],
                ['countryCode' => 'PF', 'Country' => 'French Polynesia'],
                ['countryCode' => 'TF', 'Country' => 'French Southern Territories'],
                ['countryCode' => 'GA', 'Country' => 'Gabon'],
                ['countryCode' => 'GM', 'Country' => 'Gambia'],
                ['countryCode' => 'GE', 'Country' => 'Georgia'],
                ['countryCode' => 'DE', 'Country' => 'Germany'],
                ['countryCode' => 'GH', 'Country' => 'Ghana'],
                ['countryCode' => 'GI', 'Country' => 'Gibraltar'],
                ['countryCode' => 'GR', 'Country' => 'Greece'],
                ['countryCode' => 'GL', 'Country' => 'Greenland'],
                ['countryCode' => 'GD', 'Country' => 'Grenada'],
                ['countryCode' => 'GP', 'Country' => 'Guadeloupe'],
                ['countryCode' => 'GU', 'Country' => 'Guam'],
                ['countryCode' => 'GT', 'Country' => 'Guatemala'],
                ['countryCode' => 'GG', 'Country' => 'Guernsey'],
                ['countryCode' => 'GN', 'Country' => 'Guinea'],
                ['countryCode' => 'GW', 'Country' => 'Guinea-Bissau'],
                ['countryCode' => 'GY', 'Country' => 'Guyana'],
                ['countryCode' => 'HT', 'Country' => 'Haiti'],
                ['countryCode' => 'HN', 'Country' => 'Honduras'],
                ['countryCode' => 'HK', 'Country' => 'Hong Kong SAR China'],
                ['countryCode' => 'HU', 'Country' => 'Hungary'],
                ['countryCode' => 'IS', 'Country' => 'Iceland'],
                ['countryCode' => 'IN', 'Country' => 'India'],
                ['countryCode' => 'ID', 'Country' => 'Indonesia'],
                ['countryCode' => 'IR', 'Country' => 'Iran'],
                ['countryCode' => 'IQ', 'Country' => 'Iraq'],
                ['countryCode' => 'IE', 'Country' => 'Ireland'],
                ['countryCode' => 'IM', 'Country' => 'Isle of Man'],
                ['countryCode' => 'IL', 'Country' => 'Israel'],
                ['countryCode' => 'IT', 'Country' => 'Italy'],
                ['countryCode' => 'JM', 'Country' => 'Jamaica'],
                ['countryCode' => 'JP', 'Country' => 'Japan'],
                ['countryCode' => 'JE', 'Country' => 'Jersey'],
                ['countryCode' => 'JO', 'Country' => 'Jordan'],
                ['countryCode' => 'KZ', 'Country' => 'Kazakhstan'],
                ['countryCode' => 'KE', 'Country' => 'Kenya'],
                ['countryCode' => 'KI', 'Country' => 'Kiribati'],
                ['countryCode' => 'XK', 'Country' => 'Kosovo'],
                ['countryCode' => 'KW', 'Country' => 'Kuwait'],
                ['countryCode' => 'KG', 'Country' => 'Kyrgyzstan'],
                ['countryCode' => 'LA', 'Country' => 'Laos'],
                ['countryCode' => 'LV', 'Country' => 'Latvia'],
                ['countryCode' => 'LB', 'Country' => 'Lebanon'],
                ['countryCode' => 'LS', 'Country' => 'Lesotho'],
                ['countryCode' => 'LR', 'Country' => 'Liberia'],
                ['countryCode' => 'LY', 'Country' => 'Libya'],
                ['countryCode' => 'LI', 'Country' => 'Liechtenstein'],
                ['countryCode' => 'LT', 'Country' => 'Lithuania'],
                ['countryCode' => 'LU', 'Country' => 'Luxembourg'],
                ['countryCode' => 'MO', 'Country' => 'Macau SAR China'],
                ['countryCode' => 'MK', 'Country' => 'Macedonia'],
                ['countryCode' => 'MG', 'Country' => 'Madagascar'],
                ['countryCode' => 'MW', 'Country' => 'Malawi'],
                ['countryCode' => 'MY', 'Country' => 'Malaysia'],
                ['countryCode' => 'MV', 'Country' => 'Maldives'],
                ['countryCode' => 'ML', 'Country' => 'Mali'],
                ['countryCode' => 'MT', 'Country' => 'Malta'],
                ['countryCode' => 'MH', 'Country' => 'Marshall Islands'],
                ['countryCode' => 'MQ', 'Country' => 'Martinique'],
                ['countryCode' => 'MR', 'Country' => 'Mauritania'],
                ['countryCode' => 'MU', 'Country' => 'Mauritius'],
                ['countryCode' => 'YT', 'Country' => 'Mayotte'],
                ['countryCode' => 'MX', 'Country' => 'Mexico'],
                ['countryCode' => 'FM', 'Country' => 'Micronesia'],
                ['countryCode' => 'MD', 'Country' => 'Moldova'],
                ['countryCode' => 'MC', 'Country' => 'Monaco'],
                ['countryCode' => 'MN', 'Country' => 'Mongolia'],
                ['countryCode' => 'ME', 'Country' => 'Montenegro'],
                ['countryCode' => 'MS', 'Country' => 'Montserrat'],
                ['countryCode' => 'MA', 'Country' => 'Morocco'],
                ['countryCode' => 'MZ', 'Country' => 'Mozambique'],
                ['countryCode' => 'MM', 'Country' => 'Myanmar (Burma)'],
                ['countryCode' => 'NA', 'Country' => 'Namibia'],
                ['countryCode' => 'NR', 'Country' => 'Nauru'],
                ['countryCode' => 'NP', 'Country' => 'Nepal'],
                ['countryCode' => 'NL', 'Country' => 'Netherlands'],
                ['countryCode' => 'NC', 'Country' => 'New Caledonia'],
                ['countryCode' => 'NZ', 'Country' => 'New Zealand'],
                ['countryCode' => 'NI', 'Country' => 'Nicaragua'],
                ['countryCode' => 'NE', 'Country' => 'Niger'],
                ['countryCode' => 'NG', 'Country' => 'Nigeria'],
                ['countryCode' => 'NU', 'Country' => 'Niue'],
                ['countryCode' => 'NF', 'Country' => 'Norfolk Island'],
                ['countryCode' => 'KP', 'Country' => 'North Korea'],
                ['countryCode' => 'MP', 'Country' => 'Northern Mariana Islands'],
                ['countryCode' => 'NO', 'Country' => 'Norway'],
                ['countryCode' => 'OM', 'Country' => 'Oman'],
                ['countryCode' => 'PK', 'Country' => 'Pakistan'],
                ['countryCode' => 'PW', 'Country' => 'Palau'],
                ['countryCode' => 'PS', 'Country' => 'Palestinian Territories'],
                ['countryCode' => 'PA', 'Country' => 'Panama'],
                ['countryCode' => 'PG', 'Country' => 'Papua New Guinea'],
                ['countryCode' => 'PY', 'Country' => 'Paraguay'],
                ['countryCode' => 'PE', 'Country' => 'Peru'],
                ['countryCode' => 'PH', 'Country' => 'Philippines'],
                ['countryCode' => 'PN', 'Country' => 'Pitcairn Islands'],
                ['countryCode' => 'PL', 'Country' => 'Poland'],
                ['countryCode' => 'PT', 'Country' => 'Portugal'],
                ['countryCode' => 'PR', 'Country' => 'Puerto Rico'],
                ['countryCode' => 'QA', 'Country' => 'Qatar'],
                ['countryCode' => 'RE', 'Country' => 'Réunion'],
                ['countryCode' => 'RO', 'Country' => 'Romania'],
                ['countryCode' => 'RU', 'Country' => 'Russia'],
                ['countryCode' => 'RW', 'Country' => 'Rwanda'],
                ['countryCode' => 'WS', 'Country' => 'Samoa'],
                ['countryCode' => 'SM', 'Country' => 'San Marino'],
                ['countryCode' => 'ST', 'Country' => 'São Tomé & Príncipe'],
                ['countryCode' => 'SA', 'Country' => 'Saudi Arabia'],
                ['countryCode' => 'SN', 'Country' => 'Senegal'],
                ['countryCode' => 'RS', 'Country' => 'Serbia'],
                ['countryCode' => 'SC', 'Country' => 'Seychelles'],
                ['countryCode' => 'SL', 'Country' => 'Sierra Leone'],
                ['countryCode' => 'SG', 'Country' => 'Singapore'],
                ['countryCode' => 'SX', 'Country' => 'Sint Maarten'],
                ['countryCode' => 'SK', 'Country' => 'Slovakia'],
                ['countryCode' => 'SI', 'Country' => 'Slovenia'],
                ['countryCode' => 'SB', 'Country' => 'Solomon Islands'],
                ['countryCode' => 'SO', 'Country' => 'Somalia'],
                ['countryCode' => 'ZA', 'Country' => 'South Africa'],
                ['countryCode' => 'GS', 'Country' => 'South Georgia & South Sandwich Islands'],
                ['countryCode' => 'KR', 'Country' => 'South Korea'],
                ['countryCode' => 'SS', 'Country' => 'South Sudan'],
                ['countryCode' => 'ES', 'Country' => 'Spain'],
                ['countryCode' => 'LK', 'Country' => 'Sri Lanka'],
                ['countryCode' => 'BL', 'Country' => 'St. Barthélemy'],
                ['countryCode' => 'SH', 'Country' => 'St. Helena'],
                ['countryCode' => 'KN', 'Country' => 'St. Kitts & Nevis'],
                ['countryCode' => 'LC', 'Country' => 'St. Lucia'],
                ['countryCode' => 'MF', 'Country' => 'St. Martin'],
                ['countryCode' => 'PM', 'Country' => 'St. Pierre & Miquelon'],
                ['countryCode' => 'VC', 'Country' => 'St. Vincent & Grenadines'],
                ['countryCode' => 'SD', 'Country' => 'Sudan'],
                ['countryCode' => 'SR', 'Country' => 'Suriname'],
                ['countryCode' => 'SJ', 'Country' => 'Svalbard & Jan Mayen'],
                ['countryCode' => 'SZ', 'Country' => 'Swaziland'],
                ['countryCode' => 'SE', 'Country' => 'Sweden'],
                ['countryCode' => 'CH', 'Country' => 'Switzerland'],
                ['countryCode' => 'SY', 'Country' => 'Syria'],
                ['countryCode' => 'TW', 'Country' => 'Taiwan'],
                ['countryCode' => 'TJ', 'Country' => 'Tajikistan'],
                ['countryCode' => 'TZ', 'Country' => 'Tanzania'],
                ['countryCode' => 'TH', 'Country' => 'Thailand'],
                ['countryCode' => 'TL', 'Country' => 'Timor-Leste'],
                ['countryCode' => 'TG', 'Country' => 'Togo'],
                ['countryCode' => 'TK', 'Country' => 'Tokelau'],
                ['countryCode' => 'TO', 'Country' => 'Tonga'],
                ['countryCode' => 'TT', 'Country' => 'Trinidad & Tobago'],
                ['countryCode' => 'TA', 'Country' => 'Tristan da Cunha'],
                ['countryCode' => 'TN', 'Country' => 'Tunisia'],
                ['countryCode' => 'TR', 'Country' => 'Turkey'],
                ['countryCode' => 'TM', 'Country' => 'Turkmenistan'],
                ['countryCode' => 'TC', 'Country' => 'Turks & Caicos Islands'],
                ['countryCode' => 'TV', 'Country' => 'Tuvalu'],
                ['countryCode' => 'UM', 'Country' => 'U.S. Outlying Islands'],
                ['countryCode' => 'VI', 'Country' => 'U.S. Virgin Islands'],
                ['countryCode' => 'UG', 'Country' => 'Uganda'],
                ['countryCode' => 'UA', 'Country' => 'Ukraine'],
                ['countryCode' => 'AE', 'Country' => 'United Arab Emirates'],
                ['countryCode' => 'GB', 'Country' => 'United Kingdom'],
                ['countryCode' => 'UN', 'Country' => 'United Nations'],
                ['countryCode' => 'US', 'Country' => 'United States'],
                ['countryCode' => 'UY', 'Country' => 'Uruguay'],
                ['countryCode' => 'UZ', 'Country' => 'Uzbekistan'],
                ['countryCode' => 'VU', 'Country' => 'Vanuatu'],
                ['countryCode' => 'VA', 'Country' => 'Vatican City'],
                ['countryCode' => 'VE', 'Country' => 'Venezuela'],
                ['countryCode' => 'VN', 'Country' => 'Vietnam'],
                ['countryCode' => 'WF', 'Country' => 'Wallis & Futuna'],
                ['countryCode' => 'EH', 'Country' => 'Western Sahara'],
                ['countryCode' => 'YE', 'Country' => 'Yemen'],
                ['countryCode' => 'ZM', 'Country' => 'Zambia'],
                ['countryCode' => 'ZW', 'Country' => 'Zimbabwe'],
             ];
        foreach ($arrayCountries as $country) {
            $shipping = new Shipping();
            $shipping->setCountry($country['Country']);
            $shipping->setCountryCode($country['countryCode']);
            $shipping->setPrice(null);
            $entityManager->persist($shipping);
        }

        $entityManager->flush();
    }
}
