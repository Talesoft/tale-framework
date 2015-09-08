<?php

namespace Tale\Net;

use Tale\Net\Dns\Record;
use Tale\Net\Ip\Address,
    Tale\Net\Dns\RecordType,
    Tale\Net\Dns\Record\A6,
    Tale\Net\Dns\Record\Aaaa,
    Tale\Net\Dns\Record\A,
    Tale\Net\Dns\Record\Cname,
    Tale\Net\Dns\Record\Hinfo,
    Tale\Net\Dns\Record\Mx,
    Tale\Net\Dns\Record\Ns,
    Tale\Net\Dns\Record\Ptr,
    Tale\Net\Dns\Record\Soa,
    Tale\Net\Dns\Record\Srv,
    Tale\Net\Dns\Record\Txt,
    Exception;

//http://php.net/manual/de/function.dns-get-record.php
class Dns
{

    public static function getPunyCode($hostName)
    {

        return idn_to_ascii($hostName);
    }

    public static function getUnicode($hostName)
    {

        return idn_to_utf8($hostName);
    }

    public static function getReverseHostName(Address $ipAddress)
    {

        $str = null;
        if ($ipAddress->isIpv4())
            $str = implode('.', array_reverse($ipAddress->getBytes()));
        else if ($ipAddress->isIpv6()) {

            $hexParts = $ipAddress->getHexBytes();
            $bytes = [];
            foreach ($hexParts as $part) {

                $bytes[] = $part[0];
                $bytes[] = $part[1];
            }

            $str = implode('.', array_reverse($bytes));
        } else
            throw new Exception("Failed to build reverse host name: Passed ip address needs to be either ipv4 or ipv6");

        return "$str.in-addr.arpa";
    }

    public static function hasRecordType($hostName, $type = RecordType::ANY)
    {

        return checkdnsrr($hostName, RecordType::getName($type));
    }

    public static function lookUp($hostName, $type = RecordType::ANY, $withAuthEntries = false, $withAdditionalLookups = false)
    {

        $authNs = null;
        $additionalLookups = null;
        $result = null;

        if ($withAdditionalLookups)
            $result = @dns_get_record($hostName, $type, $authNs, $additionalLookups);
        else if ($withAuthEntries)
            $result = @dns_get_record($hostName, $type, $authNs);
        else
            $result = @dns_get_record($hostName, $type);

        if (!$result)
            throw new Exception("Failed to get DNS records for $hostName, maybe there is no DNS available");

        foreach ($result as $record) {

            $type = RecordType::getValue($record['type']);
            switch ($type) {
                case RecordType::A6:

                    yield new A6($record['host'], $record['chain'], Address::fromString($record['ipv6']), $record['masklen'], $record['ttl']);
                    break;
                case RecordType::AAAA:

                    yield new Aaaa($record['host'], Address::fromString($record['ipv6']), $record['ttl']);
                    break;
                case RecordType::A:

                    yield new A($record['host'], Address::fromString($record['ip']), $record['ttl']);
                    break;
                case RecordType::CNAME:

                    yield new Cname($record['host'], $record['target'], $record['ttl']);
                    break;
                case RecordType::HINFO:

                    yield new Hinfo($record['host'], $record['cpu'], $record['os'], $record['ttl']);
                    break;
                case RecordType::MX:

                    yield new Mx($record['host'], $record['target'], $record['pri'], $record['ttl']);
                    break;
                case RecordType::NS:

                    yield new Ns($record['host'], $record['target'], $record['ttl']);
                    break;
                case RecordType::PTR:

                    yield new Ptr($record['host'], $record['target'], $record['ttl']);
                    break;
                //TODO: SRV record isn't fetched
                case RecordType::SOA:

                    yield new Soa($record['host'], $record['mname'], $record['rname'], $record['serial'], $record['refresh'], $record['retry'], $record['expire'], $record['minimum-ttl'], $record['ttl']);
                    break;
                case RecordType::TXT:

                    yield new Txt($record['host'], $record['txt'], $record['ttl']);
                    break;
                default:

                    yield new Record($record['host'], $type, $record['ttl']);
                    break;
            }
        }
    }

    public static function lookUpArray($hostName, $type = RecordType::ANY, $withAuthEntries = false, $withAdditionalLookups = false)
    {

        return iterator_to_array(self::lookUp($hostName, $type, $withAuthEntries, $withAdditionalLookups));
    }

    public static function lookUpFirst($hostName, $type = RecordType::ANY)
    {

        foreach (self::lookUp($hostName, $type) as $record)
            return $record;

        return null;
    }

    public static function lookUpARecord($hostName)
    {

        return self::lookUpFirst($hostName, RecordType::A);
    }

    public static function lookUpAaaaRecord($hostName)
    {

        return self::lookUpFirst($hostName, RecordType::AAAA);
    }

    public static function lookUpMxRecord($hostName)
    {

        return self::lookUpFirst($hostName, RecordType::MX);
    }

    public static function lookUpTxtRecord($hostName)
    {

        return self::lookUpFirst($hostName, RecordType::TXT);
    }

    public static function lookUpReverse(Address $ipAddress)
    {

        $reverseHostName = self::getReverseHostName($ipAddress);

        return self::lookUp($reverseHostName, RecordType::PTR);
    }

    public static function lookUpReverseArray(Address $ipAddress)
    {

        return iterator_to_array(self::lookUpReverse($ipAddress));
    }

    public static function lookUpReverseFirst(Address $ipAddress)
    {

        $reverseHostName = self::getReverseHostName($ipAddress);

        return self::lookUpFirst($reverseHostName, RecordType::PTR);
    }
}