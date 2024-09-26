<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encryption & Decryption App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Encryption & Decryption App</h1>
        <form method="POST" action="">
            <label for="menu">Choose an Encryption Algorithm:</label>
            <select name="menu" id="menu">
                <option value="caesar">Caesar Cipher</option>
                <option value="vigenere">Vigenere Cipher</option>
                <option value="des">DES</option>
                <option value="aes">AES</option>
                <option value="super_encryption">Super Encryption</option>
            </select>
            <label for="operation">Choose Operation:</label>
            <select name="operation" id="operation">
                <option value="encrypt">Encrypt</option>
                <option value="decrypt">Decrypt</option>
            </select>
            <label for="input_text">Enter Text:</label>
            <input type="text" name="input_text" id="input_text" required>
            <label for="key">Enter Key:</label>
            <input type="text" name="key" id="key" required>
            <button type="submit" name="submit">Submit</button>
        </form>

        <?php
        function caesar_cipher($text, $shift, $operation) {
            $result = '';
            $shift = $operation == 'encrypt' ? $shift : -$shift;
        
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                
                if (ctype_alpha($char)) {  // Periksa apakah karakter adalah huruf
                    $offset = ctype_upper($char) ? 65 : 97;  // Tentukan posisi A-Z atau a-z
                    // Konversi karakter ke kode ASCII, lakukan pergeseran, dan kembali ke karakter
                    $newChar = chr(((ord($char) - $offset + $shift) % 26 + 26) % 26 + $offset);
                    $result .= $newChar;
                } else {
                    $result .= $char;  // Jika bukan huruf, tambahkan karakter tanpa perubahan
                }
            }
            return $result;
        }
        

        function vigenere_cipher($text, $key, $operation) {
            $result = '';
            $key = strtolower($key); // Ensure key is in lowercase
            $keyLength = strlen($key);
            $keyIndex = 0;

            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];

                if (ctype_alpha($char)) {
                    $offset = ctype_upper($char) ? 65 : 97;
                    $shift = ord($key[$keyIndex % $keyLength]) - 97;
                    if ($operation == 'decrypt') {
                        $shift = -$shift;  // Fix: Apply negative shift for decryption
                    }
                    $newChar = chr(((ord($char) + $shift - $offset) % 26 + 26) % 26 + $offset);  // Fix: handling negative shifts
                    $result .= $newChar;
                    $keyIndex++;
                } else {
                    $result .= $char;
                }
            }
            return $result;
        }

        function des_encrypt($text, $key) {
            $key = substr($key, 0, 8);  // Ensure the key is 8 bytes for DES
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('DES-EDE3-CBC'));
            return openssl_encrypt($text, 'DES-EDE3-CBC', $key, 0, $iv) . "::" . bin2hex($iv);  // Return encrypted text with IV
        }

        function des_decrypt($text, $key) {
            $key = substr($key, 0, 8);  // Ensure the key is 8 bytes for DES
            list($encrypted_data, $iv) = explode('::', $text);  // Extract IV
            return openssl_decrypt($encrypted_data, 'DES-EDE3-CBC', $key, 0, hex2bin($iv));  // Decrypt using extracted IV
        }

        function aes_encrypt($text, $key) {
            $key = substr(hash('sha256', $key, true), 0, 16);  // AES-128 needs 16 bytes key
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
            return openssl_encrypt($text, 'aes-128-cbc', $key, 0, $iv) . "::" . bin2hex($iv);  // Return encrypted text with IV
        }

        function aes_decrypt($text, $key) {
            $key = substr(hash('sha256', $key, true), 0, 16);  // AES-128 uses 16-byte key
            list($encrypted_data, $iv) = explode('::', $text);  // Extract IV
            return openssl_decrypt($encrypted_data, 'aes-128-cbc', $key, 0, hex2bin($iv));  // Decrypt using extracted IV
        }

        function super_encrypt($text, $key) {
            // Apply Caesar Cipher
            $encrypted = caesar_cipher($text, 3, 'encrypt');
            // Apply Vigenere Cipher
            $encrypted = vigenere_cipher($encrypted, $key, 'encrypt');
            // Apply DES
            $encrypted = des_encrypt($encrypted, $key);
            // Apply AES
            return aes_encrypt($encrypted, $key);
        }

        function super_decrypt($text, $key) {
            // Decrypt AES
            $decrypted = aes_decrypt($text, $key);
            // Decrypt DES
            $decrypted = des_decrypt($decrypted, $key);
            // Decrypt Vigenere Cipher
            $decrypted = vigenere_cipher($decrypted, $key, 'decrypt');
            // Decrypt Caesar Cipher
            return caesar_cipher($decrypted, 3, 'decrypt');
        }

        if (isset($_POST['submit'])) {
            $menu = $_POST['menu'];
            $operation = $_POST['operation'];
            $text = $_POST['input_text'];
            $key = $_POST['key'];

            $output = '';
            switch ($menu) {
                case 'caesar':
                    $output = caesar_cipher($text, 3, $operation);
                    break;
                case 'vigenere':
                    $output = vigenere_cipher($text, $key, $operation);
                    break;
                case 'des':
                    if ($operation == 'encrypt') {
                        $output = des_encrypt($text, $key);
                    } else {
                        $output = des_decrypt($text, $key);
                    }
                    break;
                case 'aes':
                    if ($operation == 'encrypt') {
                        $output = aes_encrypt($text, $key);
                    } else {
                        $output = aes_decrypt($text, $key);
                    }
                    break;
                case 'super_encryption':
                    if ($operation == 'encrypt') {
                        $output = super_encrypt($text, $key);
                    } else {
                        $output = super_decrypt($text, $key);
                    }
                    break;
            }

            echo "<div class='result'><h2>Result:</h2><p>$output</p></div>";
        }
        ?>
    </div>
</body>
</html>