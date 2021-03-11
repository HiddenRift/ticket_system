<?php


namespace SupportTicketManager;
include_once __DIR__ . '/SimilarityChecker.php';

/** Compares two strings  using the built in similar text method
 * Class PhpComparer
 * @package SupportTicketManager
 */
class PhpComparer extends SimilarityChecker
{
    /** compares two strings for their similarity
     * @param string $text1
     * @param string $text2
     * @return float result >= 0 and  <= 100
     */
    public static function compareStrings(string $text1, string $text2):float
    {
        similar_text($text1, $text2, $similarity);
        return $similarity;
    }
}