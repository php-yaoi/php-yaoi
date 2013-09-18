<?php

class Http_MaliceChecker extends Base_Class {
    public $stopWords = array(
        array('androgyne', 'asslicking', 'analingus', 'anilingus', 'blowjob', 'bondage', 'bukkake', 'bdsm', 'bisexual', 'clitoris', 'cocksucker', 'creampie', 'cumshot', 'copulation', 'coitus', 'cunnilingus', 'cybersex', 'dildo', 'doggystyle', 'exhibitionis', 'ejaculation', 'fuck', 'fellatio', 'gangbang', 'groupsex', 'handjob', 'incest', 'homosexual', 'lesbian', 'masturbation', 'orgasm', 'pornstar', 'porno', 'prostitute', 'pedophil', 'pthc', 'pissing', 'sluts', 'transvesti', 'transgender', 'transsexual', 'upskirt', 'voyeur', 'vagina', 'vulva', 'whore',
        'pharmacy', 'drugs', 'pills', 'alertec', 'alertex', 'accutane', 'cialis', 'cocaine', 'cymbalta', 'celebrex', 'citalopram', 'diclofenac', 'doxycycline', 'erectile', 'effexor', 'flomax', 'hydrocodone', 'hoodia', 'levitra', 'lipitor', 'lisinopril', 'marijuana', 'modalert', 'modasomil', 'modafinil', 'moderateafinil', 'modiodal', 'modulertnexium', 'phentermine', 'propecia', 'prozac', 'prednisone', 'poppers', 'provigil', 'salvia', 'simvastatin', 'tramadol', 'topamax', 'ultram', 'valium', 'valtrex', 'viagra', 'vigicer', 'vigil', 'wellbutrin', 'xanax', 'zoloft', 'zithromax',
        'gambling', 'casino', 'poker', 'blackjack', 'betting', 'baccarat', 'bookmaker', 'jackpot', 'roulette', 'lotteries', 'videopoker'),
    );

    public $uriList = array();

    public function __construct($uriList) {
        $this->uriList = $uriList;
    }


    /**
     * @param null $clean
     * @param null $malicious
     */
    public function check(&$clean = array(), &$malicious = array()) {

        foreach ($this->uriList as $uri) {
            //$h = new Http_Client();
            //$h->url = $uri;
            //$result = $h->fetch();
            $result = '';

            $filtered = str_replace($this->stopWords, '', $result);
            if ($filtered != $result) {
                $malicious []= $uri;
            }
            else {
                $clean []= $uri;
            }
        }
    }

}