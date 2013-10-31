<?php
/**
 * Class Mock_StrictSequenceData
 *
 * Capture and play strictly sequenced protocol scenarios.
 */
class Mock_StrictSequenceData implements Mock_DataSet {
    private $play = false;
    private $capture = false;

    /**
     * @var Storage_Client
     */
    private $storage;

    private $sequenceId = 0;

    public function add($key, $value)
    {
        if (!$this->captureActive()) {
            throw new Mock_Exception('Capture not started', Mock_Exception::CAPTURE_REQUIRED);
        }
        $this->storage->set($this->sequenceId . $key, $value);
        ++$this->sequenceId;
    }

    public function get($key = null)
    {
        if (!$this->playActive()) {
            throw new Mock_Exception('Playback not started', Mock_Exception::PLAY_REQUIRED);
        }

        if (null === $item = $this->storage->get($this->sequenceId . $key)) {
            throw new Mock_Exception('Invalid key, "' . $key . '" not found at offset ' . $this->sequenceId,
                Mock_Exception::KEY_NOT_FOUND);
        }
    }

    public function capture(Storage_Client $data)
    {
        $this->storage = $data;
        $this->capture = true;
    }

    public function play(Storage_Client $data)
    {
        $this->storage = $data;
        $this->play = true;
    }

    public function playActive()
    {
        return $this->play || !$this->capture;
    }

    public function captureActive()
    {
        return $this->capture || !$this->play;
    }


}