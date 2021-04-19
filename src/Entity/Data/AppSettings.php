<?php

declare(strict_types=1);


namespace App\Entity\Data;


class AppSettings
{
    private array $documentsToDisplayInApp;
    private bool $toolScanQrCodeOnReturn;
    private bool $keyyScanQrCodeOnReturn;
    private bool $saveNoteOnManualAddToShoppingBasket;
    private bool $saveImageToNativeLibrary;
    private bool $saveBookingNote;
    private bool $saveBookingProject;
    
    public function __construct(
        ?array $documentsToDisplayInApp,
        ?bool $toolScanQrCodeOnReturn,
        ?bool $keyyScanQrCodeOnReturn,
        ?bool $saveNoteOnManualAddToShoppingBasket,
        ?bool $saveImageToNativeLibrary,
        ?bool $saveBookingNote,
        ?bool $saveBookingProject
    )
    {
        $this->documentsToDisplayInApp = $documentsToDisplayInApp ?? [];
        $this->toolScanQrCodeOnReturn = $toolScanQrCodeOnReturn ?? false;
        $this->keyyScanQrCodeOnReturn = $keyyScanQrCodeOnReturn ?? false;
        $this->saveNoteOnManualAddToShoppingBasket = $saveNoteOnManualAddToShoppingBasket ?? false;
        $this->saveImageToNativeLibrary = $saveImageToNativeLibrary ?? false;
        $this->saveBookingNote = $saveBookingNote ?? false;
        $this->saveBookingProject = $saveBookingProject ?? false;
    }
    
    public function toArray(): array
    {
        return [
            'documentsToDisplayInApp' => $this->documentsToDisplayInApp,
            'toolScanQrCodeOnReturn' => $this->toolScanQrCodeOnReturn,
            'keyyScanQrCodeOnReturn' => $this->keyyScanQrCodeOnReturn,
            'saveNoteOnManualAddToShoppingBasket' => $this->saveNoteOnManualAddToShoppingBasket,
            'saveImageToNativeLibrary' => $this->saveImageToNativeLibrary,
            'saveBookingNote' => $this->saveBookingNote,
            'saveBookingProject' => $this->saveBookingProject
        ];
    }
    
    public static function fromArray(array $appSettingsArray): self
    {
        return new self(
            $appSettingsArray['documentsToDisplayInApp'] ?? [],
            $appSettingsArray['toolScanQrCodeOnReturn'] ?? false,
            $appSettingsArray['keyyScanQrCodeOnReturn'] ?? false,
            $appSettingsArray['saveNoteOnManualAddToShoppingBasket'] ?? false,
            $appSettingsArray['saveImageToNativeLibrary'] ?? false,
            $appSettingsArray['saveBookingNote'] ?? false,
            $appSettingsArray['saveBookingProject'] ?? false
        );
    }
    
    public function getDocumentsToDisplayInApp(): ?array
    {
        return $this->documentsToDisplayInApp;
    }
    
    public function setDocumentsToDisplayInApp(?array $documentsToDisplayInApp): void
    {
        $this->documentsToDisplayInApp = $documentsToDisplayInApp;
    }
    
    public function getToolScanQrCodeOnReturn(): ?bool
    {
        return $this->toolScanQrCodeOnReturn;
    }
    
    public function setToolScanQrCodeOnReturn(?bool $toolScanQrCodeOnReturn): void
    {
        $this->toolScanQrCodeOnReturn = $toolScanQrCodeOnReturn;
    }
    
    public function getKeyyScanQrCodeOnReturn(): ?bool
    {
        return $this->keyyScanQrCodeOnReturn;
    }
    
    public function setKeyyScanQrCodeOnReturn(?bool $keyyScanQrCodeOnReturn): void
    {
        $this->keyyScanQrCodeOnReturn = $keyyScanQrCodeOnReturn;
    }
    
    public function getSaveNoteOnManualAddToShoppingBasket(): ?bool
    {
        return $this->saveNoteOnManualAddToShoppingBasket;
    }
    
    public function setSaveNoteOnManualAddToShoppingBasket(?bool $saveNoteOnManualAddToShoppingBasket): void
    {
        $this->saveNoteOnManualAddToShoppingBasket = $saveNoteOnManualAddToShoppingBasket;
    }
    
    public function getSaveImageToNativeLibrary(): ?bool
    {
        return $this->saveImageToNativeLibrary;
    }
    
    public function setSaveImageToNativeLibrary(?bool $saveImageToNativeLibrary): void
    {
        $this->saveImageToNativeLibrary = $saveImageToNativeLibrary;
    }
    
    public function getSaveBookingNote(): ?bool
    {
        return $this->saveBookingNote;
    }
    
    public function setSaveBookingNote(?bool $saveBookingNote): void
    {
        $this->saveBookingNote = $saveBookingNote;
    }
    
    public function getSaveBookingProject(): ?bool
    {
        return $this->saveBookingProject;
    }
    
    public function setSaveBookingProject(?bool $saveBookingProject): void
    {
        $this->saveBookingProject = $saveBookingProject;
    }
}
