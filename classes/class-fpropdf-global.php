<?php

class Fpropdf_Global {

    protected $attachments;

    function __construct() {
        $this->flush();
    }

    /*
     * Add attachment to remove
     * @param $attachment - Filepath to attachment
     */

    function addAttachmentToRemove($attachment) {
        $this->attachments[] = $attachment;
    }

    /*
     * Get attachments to remove
     * @return array()
     */

    function getAttachmentsToRemove() {
        return $this->attachments;
    }

    /*
     * Flush all settings
     */

    function flush() {
        $this->attachments = array();
    }

}
