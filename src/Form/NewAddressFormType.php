<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/27/19
 * Time: 1:08 PM
 */

namespace App\Form;

use App\Entity\UserAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class NewAddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries  = ['Afghanistan', 'Åland Islands', 'Albania', 'Algeria', 'American Samoa',
            'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua & Barbuda', 'Argentina', 'Armenia',
            'Aruba', 'Ascension Island', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain',
            'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia',
            'Bosnia & Herzegovina', 'Botswana', 'Brazil', 'British Indian Ocean Territory', 'British Virgin Islands',
            'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Canary Islands',
            'Cape Verde', 'Caribbean Netherlands', 'Cayman Islands', 'Central African Republic', 'Ceuta & Melilla',
            'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros',
            'Congo - Brazzaville', 'Congo - Kinshasa', 'Cook Islands', 'Costa Rica', 'Côte d’Ivoire', 'Croatia',
            'Cuba', 'Curaçao', 'Cyprus', 'Czechia', 'Denmark', 'Diego Garcia', 'Djibouti', 'Dominica',
            'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia',
            'Ethiopia', 'Eurozone', 'Falkland Islands', 'Faroe Islands', 'Fiji', 'Finland', 'France',
            'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia',
            'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala',
            'Guernsey', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hong Kong SAR China', 'Hungary',
            'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica',
            'Japan', 'Jersey', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos',
            'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg',
            'Macau SAR China', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta',
            'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia', 'Moldova',
            'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar (Burma)', 'Namibia',
            'Nauru', 'Nepal', 'Netherlands', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue',
            'Norfolk Island', 'North Korea', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau',
            'Palestinian Territories', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines',
            'Pitcairn Islands', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Réunion', 'Romania', 'Russia', 'Rwanda',
            'Samoa', 'San Marino', 'São Tomé & Príncipe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
            'Sierra Leone', 'Singapore', 'Sint Maarten', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia',
            'South Africa', 'South Georgia & South Sandwich Islands', 'South Korea', 'South Sudan', 'Spain',
            'Sri Lanka', 'St. Barthélemy', 'St. Helena', 'St. Kitts & Nevis', 'St. Lucia', 'St. Martin',
            'St. Pierre & Miquelon', 'St. Vincent & Grenadines', 'Sudan', 'Suriname', 'Svalbard & Jan Mayen',
            'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand',
            'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad & Tobago', 'Tristan da Cunha', 'Tunisia', 'Turkey',
            'Turkmenistan', 'Turks & Caicos Islands', 'Tuvalu', 'U.S. Outlying Islands', 'U.S. Virgin Islands',
            'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United Nations', 'United States',
            'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Wallis & Futuna',
            'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'
        ];

        $builder
            ->add(
                'addressFirst',
                TextType::class,
                [
                    'label' => 'Address line 1: '
                ]
            )
            ->add(
                'addressSecond',
                TextType::class,
                [
                    'label' => 'Address line 2: '
                ]
            )
            ->add(
                'country',
                ChoiceType::class,
                [
                    'label' => 'Country: ',
                    'choices' => $countries,
                    'choice_label' => function ($value) {
                        return $value;
                    },
                ]
            )
            ->add(
                'state',
                TextType::class,
                [
                    'label' => 'State / Province / Region: '
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label' => 'City / Town: '
                ]
            )
            ->add(
                'postalCode',
                IntegerType::class,
                [
                    'label' => 'ZIP / Postal code: '
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserAddress::class,
            ]
        );
    }
}
