<?php

/**
 * Based off the default SquizLabs ClassCommentSniff.
 *
 * Changes
 *  - Allows DocBlock tags.
 *  - Disallows the use of Doctrine's ORM Namespace.
 */

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class ClassCommentSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_CLASS];

    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]['code'] !== T_COMMENT){
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');
            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $error = 'There must be no blank lines after the class comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {

            if(substr($tokens[$tag]['content'], 0, 5) == '@ORM\\'){
                $phpcsFile->addError('Entity annotated using the ' . $tokens[$tag]['content'] . ' Namespace, import the Annotation class instead.', $tag, 'ImportDoctrineORMFully');
            }
        }
    }
}
