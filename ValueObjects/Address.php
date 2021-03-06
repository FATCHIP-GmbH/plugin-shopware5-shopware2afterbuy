<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\ValueObjects;

use DateTime;
use JsonSerializable;

class Address extends AbstractValueObject implements JsonSerializable
{
    /**
     * Contains the name of the address address company
     *
     * @var string
     */
    protected $company = '';

    /**
     * Contains the department name of the address address company
     *
     * @var string
     */
    protected $department = '';

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
    protected $phone = '';

    /**
     * Contains the vat id of the address
     *
     * @var string
     */
    protected $vatId = '';

    /**
     * Contains the additional address line data
     *
     * @var string
     */
    protected $additionalAddressLine1 = '';

    /**
     * Contains the additional address line data 2
     *
     * @var string
     */
    protected $additionalAddressLine2 = '';

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
     * @var DateTime
     */
    protected $birthday;

    /**
     * @return array
     */
    function jsonSerialize() {
        return [
            'company' => $this->company,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'street' => $this->street,
            'additionalAddressLine1' => $this->additionalAddressLine1,
            'zip' => $this->zipcode,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone
        ];
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param DateTime $birthday
     */
    public function setBirthday(DateTime $birthday)
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
     * @noinspection PhpUnused
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
     * @noinspection PhpUnused
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
            $vatId = '';
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
     * @noinspection PhpUnused
     * @return string
     */
    public function getAdditionalAddressLine2()
    {
        return $this->additionalAddressLine2;
    }

    /**
     * @noinspection PhpUnused
     * @param string $additionalAddressLine2
     */
    public function setAdditionalAddressLine2(string $additionalAddressLine2)
    {
        if(!$additionalAddressLine2) {
            $additionalAddressLine2 = '';
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
    public function setEmail(string $email)
    {
        if(!$email) {
            return;
        }

        $this->email = $email;
    }

    /**
     * used to compare billing and shippings address by ignoring several properties. relevant properties are defined in jsonSerialize
     *
     * @param Address $addr
     * @return bool
     */
    public function compare(Address $addr) {
        return json_encode($this) === json_encode($addr);
    }
}
