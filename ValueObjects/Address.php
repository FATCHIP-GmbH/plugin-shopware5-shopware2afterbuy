<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace abaccAfterbuy\ValueObjects;

class Address extends AbstractValueObject
{
    /**
     * Contains the name of the address address company
     *
     * @var string
     */
    protected $company = "";

    /**
     * Contains the department name of the address address company
     *
     * @var string
     */
    protected $department = "";

    /**
     * Contains the customer salutation (Mr, Ms, Company)
     *
     * @var string
     */
    protected $salutation = '';

    /**
     * Contains the first name of the address
     *
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $title;

    /**
     * Contains the last name of the address
     *
     * @var string
     */
    protected $lastname;

    /**
     * Contains the street name of the address
     *
     * @var string
     */
    protected $street;

    /**
     * Contains the zip code of the address
     *
     * @var string
     */
    protected $zipcode;

    /**
     * Contains the city name of the address
     *
     * @var string
     */
    protected $city;

    /**
     * Contains the phone number of the address
     *
     * @var string
     */
    protected $phone = "";

    /**
     * Contains the vat id of the address
     *
     * @var string
     */
    protected $vatId = "";

    /**
     * Contains the additional address line data
     *
     * @var string
     */
    protected $additionalAddressLine1 = "";

    /**
     * Contains the additional address line data 2
     *
     * @var string
     */
    protected $additionalAddressLine2 = "";

    /**
     * Contains the iso of the country.
     *
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $email = 'no.mail@set.org';

    /**
     * @var \DateTime
     */
    protected $birthday;

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday(\DateTime $birthday)
    {
        $this->birthday = $birthday;
    }



    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param string $department
     */
    public function setDepartment(string $department)
    {
        $this->department = $department;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     */
    public function setSalutation(string $salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getVatId()
    {
        return $this->vatId;
    }

    /**
     * @param string $vatId
     */
    public function setVatId($vatId)
    {
        $vatId = (string) $vatId;
        if(!$vatId) {
            $vatId = "";
        }

        $this->vatId = $vatId;
    }

    /**
     * @return string
     */
    public function getAdditionalAddressLine1()
    {
        return $this->additionalAddressLine1;
    }

    /**
     * @param string $additionalAddressLine1
     */
    public function setAdditionalAddressLine1(string $additionalAddressLine1)
    {
        $this->additionalAddressLine1 = $additionalAddressLine1;
    }

    /**
     * @return string
     */
    public function getAdditionalAddressLine2()
    {
        return $this->additionalAddressLine2;
    }

    /**
     * @param string $additionalAddressLine2
     */
    public function setAdditionalAddressLine2(string $additionalAddressLine2)
    {
        if(!$additionalAddressLine2) {
            $additionalAddressLine2 = "";
        }

        $this->additionalAddressLine2 = $additionalAddressLine2;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email)
    {
        if(!$email) {
            return;
        }

        $this->email = $email;
    }


}
