<?php

declare(strict_types=1);

eval('declare(strict_types=1);namespace Govee {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php') . '}');
eval('declare(strict_types=1);namespace Govee {?>' . file_get_contents(__DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php') . '}');

    class GoveeDevice extends IPSModule
    {
        use \Govee\ColorHelper;
        use \Govee\VariableProfileHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
            $this->RegisterPropertyBoolean('Active', false);
            $this->RegisterPropertyInteger('Interval', 10);

            $this->RegisterVariableBoolean('State', $this->Translate('State'), '~Switch', 0);
            $this->EnableAction('State');
            $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 0);
            $this->EnableAction('Brightness');
            $this->RegisterVariableInteger('Color', $this->Translate('Color'), '~HexColor', 0);
            $this->EnableAction('Color');
            $this->RegisterProfileInteger('Govee.ColorTemperature', 'Intensity', '', ' K', 2000, 9000, 1);
            $this->RegisterVariableInteger('ColorTemperature', $this->Translate('Color Temperature'), 'Govee.ColorTemperature', 0);
            $this->EnableAction('ColorTemperature');

            $this->RegisterTimer('GOVEE_UpdateState', 0, 'GOVEE_UpdateState($_IPS[\'TARGET\']);');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
            if ($this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('GOVEE_UpdateState', $this->ReadPropertyInteger('Interval') * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('GOVEE_UpdateState', 0);
                $this->SetStatus(104);
            }
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'State':
                    $this->setState($Value);
                    break;
                case 'Brightness':
                    $this->setBrightness($Value);
                    break;
                case 'Color':
                    $this->setColor($Value);
                    break;
                case 'ColorTemperature':
                    $this->setColorTemperature($Value);
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'Invalid Action: ' . $Ident, 0);
                    break;
            }
        }

        public function SendData(string $Payload)
        {
            $this->SendDebug(__FUNCTION__ . ' :: Payload', $Payload, 0);

            if ($this->HasActiveParent()) {
                $this->SendDataToParent(json_encode(['DataID' => '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}', 'Buffer' => $Payload]));
            }
        }

        public function UpdateState()
        {
            $Payload = [
                'msg' => [
                    'cmd'  => 'devStatus',
                    'data' => [
                    ]
                ]
            ];
            $this->SendData(json_encode($Payload));
        }

        public function ReceiveData($JSONString)
        {
            $data = json_decode($JSONString, true);
            $buffer = json_decode($data['Buffer'], true);
            IPS_LogMessage('test', print_r($buffer, true));
            $deviceData = $buffer['msg']['data'];

            $this->SetValue('State', $deviceData['onOff']);
            $this->SetValue('Brightness', $deviceData['brightness']);

            $color = $this->RGBToHex($deviceData['color']['r'], $deviceData['color']['g'], $deviceData['color']['b']);
            $this->SetValue('Color', $color);
            $this->SetValue('ColorTemperature', $deviceData['colorTemInKelvin']);
        }

        private function setState(bool $state)
        {
            {
                $Payload = [
                    'msg' => [
                        'cmd'  => 'turn',
                        'data' => [
                            'value' => intval($state)
                        ]
                    ]
                ];
                $this->SendData(json_encode($Payload));
                IPS_Sleep(1000);
                $this->UpdateState();
            }
        }

        private function setBrightness(int $brightness)
        {
            {
                $Payload = [
                    'msg' => [
                        'cmd'  => 'brightness',
                        'data' => [
                            'value' => $brightness
                        ]
                    ]
                ];

                $this->SendData(json_encode($Payload));
                IPS_Sleep(1000);
                $this->UpdateState();

                }
        }

        private function setColor(int $color)
        {
            $rgb = $this->HexToRGB($color);

            {
                $Payload = [
                    'msg' => [
                        'cmd'  => 'colorwc',
                        'data' => [
                            'color' => [
                                'r'=> $rgb[0],
                                'g'=> $rgb[1],
                                'b'=> $rgb[2],
                            ],
                            'colorTemInKelvin'=> 0
                        ]
                    ]
                ];
                $this->SendData(json_encode($Payload));
                IPS_Sleep(1000);
                $this->UpdateState();
            }
        }

        private function setColorTemperature(int $ct)
        {
            {
                $Payload = [
                    'msg' => [
                        'cmd'  => 'colorwc',
                        'data' => [
                            'color' => [
                                'r'=> 0,
                                'g'=> 0,
                                'b'=> 0,
                            ],
                            'colorTemInKelvin'=> $ct
                        ]
                    ]
                ];
                $this->SendData(json_encode($Payload));
                IPS_Sleep(1000);
                $this->UpdateState();
            }
        }
    }

