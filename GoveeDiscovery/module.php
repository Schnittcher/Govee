<?php

declare(strict_types=1);
    class GoveeDiscovery extends IPSModule
    {
        public function Create()
        {
//            $this->RequireParent('{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}');
            $this->RequireParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');

            $this->RegisterAttributeString('Devices', '{}');
            parent::Create();
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }
        /**
         * public function GetConfigurationForParent()
         * {
         * $settings = [
         * 'BindPort'           => 4002,
         * 'EnableBroadcast'    => true,
         * 'EnableLoopback'     => false,
         * 'EnableReuseAddress' => true,
         * 'Host'               => '',
         * 'MulticastIP'        => '239.255.255.250',
         * 'Port'               => 4001
         * ];
         *
         * return json_encode($settings, JSON_UNESCAPED_SLASHES);
         * }
         */
        public function GetConfigurationForParent()
        {
            $settings = [
                'BindPort'           => 4002,
                'EnableBroadcast'    => true,
                'EnableReuseAddress' => true,
            ];

            return json_encode($settings, JSON_UNESCAPED_SLASHES);
        }

        public function SendData(string $Payload)
        {
            $this->SendDataToParent(json_encode([
                'DataID'     => '{8E4D9B23-E0F2-1E05-41D8-C21EA53B8706}',
                'Buffer'     => $Payload, //utf8_encode("Hallo Welt String"),
                'ClientIP'   => '239.255.255.250',
                'ClientPort' => 4001,
                'Broadcast'  => false,
            ]));
        }

        public function scanDevices()
        {
            $Payload = [
                'msg' => [
                    'cmd'  => 'scan',
                    'data' => [
                        'account_topic' => 'reserve'
                    ]
                ]
            ];
            if ($this->HasActiveParent()) {
                $this->SendData(json_encode($Payload));
                return;

                $this->SendDataToParent(json_encode([
                    'DataID' => '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}',
                    'Buffer' => utf8_encode(json_encode($Payload))
                ]));
            }
        }

        public function ReceiveData($JSONString)
        {
            $data = json_decode($JSONString, true);
            $devices = json_decode($this->ReadAttributeString('Devices'), true);

            $buffer = json_decode($data['Buffer'], true);
            $data = $buffer['msg']['data'];
            $tmpDevice = [];

            //IPS_LogMessage('test', print_r($devices, true));
            if (array_key_exists('device', $data)) {
                if (!array_key_exists($data['device'], $devices)) {
                    $devices[$data['device']] = [
                        'ip'              => $data['ip'],
                        'sku'             => $data['sku'],
                        'bleVersionHard'  => $data['bleVersionHard'],
                        'bleVersionSoft'  => $data['bleVersionSoft'],
                        'wifiVersionHard' => $data['wifiVersionHard'],
                        'wifiVersionSoft' => $data['wifiVersionSoft']
                    ];
                }
            }
            $this->WriteAttributeString('Devices', json_encode($devices));
        }

        public function GetConfigurationForm()
        {
            $this->scanDevices();
            $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $devices = json_decode($this->ReadAttributeString('Devices'), true);

            $Values = [];
            foreach ($devices as $key => $device) {
                $Values[] = [
                    'IP'                          => $device['ip'],
                    'MAC'                         => $key,
                    'SKU'                         => $device['sku'],
                    'bleVersionHard'              => $device['bleVersionHard'],
                    'bleVersionSoft'              => $device['bleVersionSoft'],
                    'wifiVersionHard'             => $device['wifiVersionHard'],
                    'wifiVersionSoft'             => $device['wifiVersionSoft'],
                    'instanceID'                  => $this->getInstanceID($device['ip']),
                    'create'                      => [
                        [
                            'moduleID'      => '{BFF4858B-78B1-B4AD-B755-24AEC44EACFF}', //Device
                            'configuration' => [
                                'Host'   => $device['ip'],
                                'Active' => true
                            ]
                        ],
                        [
                            'moduleID'      => '{82347F20-F541-41E1-AC5B-A636FD3AE2D8}', //Device
                            'configuration' => [
                                'BindPort'           => 4002,
                                'Open'               => true,
                                'EnableReuseAddress' => true,
                                'EnableBroadcast'    => true
                            ]
                        ]
                    ]
                ];
            }
            $Form['actions'][0]['values'] = $Values;
            return json_encode($Form);
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }

        private function getInstanceID($IP)
        {
            $InstanceIDs = IPS_GetInstanceListByModuleID('{BFF4858B-78B1-B4AD-B755-24AEC44EACFF}');
            foreach ($InstanceIDs as $id) {
                $ParentIP = IPS_GetProperty($id, 'Host');
                if ($ParentIP == $IP) {
                    return $id;
                }
            }
            return 0;
        }
    }