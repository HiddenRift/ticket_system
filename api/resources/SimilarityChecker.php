<?php


namespace SupportTicketManager;


/** Abstract similarity checker to provide a common template that can be used for all
 * classes that implement the same functionality
 * Class SimilarityChecker
 * @package SupportTicketManager
 */
abstract class SimilarityChecker
{
    /**
     * @param string $text1
     * @param string $text2
     * @return float similarity value, higher representing a closer match
     */
    abstract public static function compareStrings(string $text1, string $text2):float;
}