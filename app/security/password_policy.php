<?php

class PasswordPolicy {
    private static $commonPasswords = [
        'password', 'password123', '123456', '12345678', '123456789', '12345', '1234567',
        'qwerty', 'abc123', '111111', '1234567890', 'senha', '1234', 'qwerty123',
        'default', 'admin', 'letmein', 'welcome', 'monkey', 'dragon', 'master',
        'sunshine', 'princess', 'football', 'login', 'passw0rd', 'starwars',
        'whatever', 'password1', 'trustno1', 'batman', 'shadow', 'superman',
        'michael', 'ashley', 'bailey', 'nicole', 'jessica', 'qwertyuiop',
        'iloveyou', 'welcome123', 'admin123', 'root', 'toor', 'pass123',
        'azerty', 'qwerty123456', 'password!', 'Password1', 'Password123',
        'p@ssw0rd', 'P@ssw0rd', 'p@ssword', 'P@ssword', 'admin@123',
        'root@123', 'test123', 'Test123', 'demo123', 'Demo123', 'user123',
        'User123', 'changeme', 'ChangeMe', 'welcome1', 'Welcome1',
        '123qwe', 'qwe123', 'abc@123', 'Abc@123', '1q2w3e4r', '1q2w3e4r5t',
        'zxcvbnm', 'asdfghjkl', 'qazwsx', 'qazwsxedc', 'a1b2c3d4',
        'password01', 'password02', 'password03', 'temp123', 'Temp123',
        'guest', 'Guest123', 'demo', 'Demo', 'sample', 'Sample123',
        'test', 'Test', 'testing', 'Testing123', 'example', 'Example123',
        'mypassword', 'MyPassword', 'secret', 'Secret123', 'access', 'Access123',
        'killer', 'Killer123', 'hockey', 'Hockey123', 'computer', 'Computer123',
        'internet', 'Internet123', 'mustang', 'Mustang123', 'ferrari', 'Ferrari123',
        'fuckyou', 'trustno1', 'ranger', 'Ranger123', 'jordan', 'Jordan123',
        'buster', 'Buster123', 'hunter', 'Hunter123', 'thomas', 'Thomas123',
        'robert', 'Robert123', 'pepper', 'Pepper123', 'killer123', 'Master123',
        '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999', '0000',
        '11111', '22222', '33333', '44444', '55555', '66666', '77777', '88888', '99999', '00000',
        '123123', '321321', '456456', '654654', '789789', '987987',
        'aa123456', 'aa123456789', 'abc12345', 'abc123456', 'abcd1234',
        'pass1234', 'pass12345', 'pass123456', 'pass@123', 'pass@1234',
        'qwer1234', 'asdf1234', 'zxcv1234', '1qaz2wsx', '1qazxsw2',
        'qweasd', 'qweasdzxc', 'zaq12wsx', 'xsw23edc', 'cde34rfv',
        'vfr45tgb', 'bgt56yhn', 'yhn67ujm', 'mju78ik', 'ik89ol',
        'pol90', 'passpass', 'adminadmin', 'rootroot', 'testtest',
        '000000', '0000000', '00000000', '000000000', '0123456789',
        '987654321', '9876543210', '1029384756', '0987654321',
        'baseball', 'Baseball123', 'football1', 'Football1', 'soccer', 'Soccer123',
        'basketball', 'Basketball1', 'running', 'Running123', 'swimming', 'Swimming123',
        'dragon123', 'Dragon123', 'shadow123', 'Shadow123', 'sunshine1', 'Sunshine1',
        'buttercup', 'Buttercup1', 'ginger', 'Ginger123', 'cookie', 'Cookie123',
        'lovers', 'Lovers123', 'princess1', 'Princess1', 'angel', 'Angel123',
        'sexy', 'Sexy123', 'hottie', 'Hottie123', 'loveyou', 'LoveYou1',
        'love123', 'Love123', 'friends', 'Friends1', 'family', 'Family123',
        'happy', 'Happy123', 'smile', 'Smile123', 'golden', 'Golden123',
        'flower', 'Flower123', 'purple', 'Purple123', 'pink', 'Pink123',
        'blue', 'Blue123', 'green', 'Green123', 'red', 'Red123',
        'orange', 'Orange123', 'yellow', 'Yellow123', 'silver', 'Silver123',
        'mercedes', 'Mercedes1', 'porsche', 'Porsche1', 'bmw123', 'BMW123',
        'audi', 'Audi123', 'toyota', 'Toyota123', 'honda', 'Honda123',
        'nissan', 'Nissan123', 'mazda', 'Mazda123', 'subaru', 'Subaru123',
        'corvette', 'Corvette1', 'camaro', 'Camaro123', 'challenger', 'Challenger1',
        'windows', 'Windows1', 'microsoft', 'Microsoft1', 'google', 'Google123',
        'facebook', 'Facebook1', 'twitter', 'Twitter1', 'instagram', 'Instagram1',
        'linkedin', 'LinkedIn1', 'youtube', 'YouTube1', 'amazon', 'Amazon123',
        'apple', 'Apple123', 'samsung', 'Samsung1', 'nokia', 'Nokia123',
        'dolphin', 'Dolphin1', 'elephant', 'Elephant1', 'tiger', 'Tiger123',
        'lion', 'Lion123', 'bear', 'Bear123', 'wolf', 'Wolf123',
        'eagle', 'Eagle123', 'shark', 'Shark123', 'snake', 'Snake123',
        'spider', 'Spider123', 'scorpion', 'Scorpion1', 'panther', 'Panther1',
        'jaguar', 'Jaguar123', 'leopard', 'Leopard1', 'cheetah', 'Cheetah1',
        'monkey123', 'Monkey123', 'gorilla', 'Gorilla1', 'panda', 'Panda123',
        'koala', 'Koala123', 'penguin', 'Penguin1', 'parrot', 'Parrot123',
        'falcon', 'Falcon123', 'hawk', 'Hawk123', 'raven', 'Raven123',
        'sparrow', 'Sparrow1', 'robin', 'Robin123', 'bluebird', 'BlueBird1',
        'cardinal', 'Cardinal1', 'dove', 'Dove123', 'swan', 'Swan123',
        'guitar', 'Guitar123', 'piano', 'Piano123', 'drums', 'Drums123',
        'violin', 'Violin123', 'trumpet', 'Trumpet1', 'saxophone', 'Saxophone1',
        'flute', 'Flute123', 'clarinet', 'Clarinet1', 'trombone', 'Trombone1',
        'harmonica', 'Harmonica1', 'banjo', 'Banjo123', 'ukulele', 'Ukulele1',
        'january', 'January1', 'february', 'February1', 'march', 'March123',
        'april', 'April123', 'may123', 'May123', 'june', 'June123',
        'july', 'July123', 'august', 'August123', 'september', 'September1',
        'october', 'October1', 'november', 'November1', 'december', 'December1',
        'monday', 'Monday123', 'tuesday', 'Tuesday1', 'wednesday', 'Wednesday1',
        'thursday', 'Thursday1', 'friday', 'Friday123', 'saturday', 'Saturday1',
        'sunday', 'Sunday123', 'weekend', 'Weekend1', 'holiday', 'Holiday1',
        'summer', 'Summer123', 'winter', 'Winter123', 'spring', 'Spring123',
        'autumn', 'Autumn123', 'fall', 'Fall123', 'season', 'Season123',
        'morning', 'Morning1', 'evening', 'Evening1', 'night', 'Night123',
        'midnight', 'Midnight1', 'sunrise', 'Sunrise1', 'sunset', 'Sunset123',
        'coffee', 'Coffee123', 'tea123', 'Tea123', 'beer', 'Beer123',
        'wine', 'Wine123', 'vodka', 'Vodka123', 'whiskey', 'Whiskey1',
        'brandy', 'Brandy123', 'champagne', 'Champagne1', 'cocktail', 'Cocktail1',
        'juice', 'Juice123', 'water', 'Water123', 'milk', 'Milk123',
        'pizza', 'Pizza123', 'burger', 'Burger123', 'pasta', 'Pasta123',
        'sushi', 'Sushi123', 'steak', 'Steak123', 'chicken', 'Chicken1',
        'salmon', 'Salmon123', 'tuna', 'Tuna123', 'bacon', 'Bacon123',
        'cheese', 'Cheese123', 'chocolate', 'Chocolate1', 'vanilla', 'Vanilla1',
        'strawberry', 'Strawberry1', 'banana', 'Banana123', 'apple123', 'Apple1',
        'orange123', 'Orange1', 'grape', 'Grape123', 'melon', 'Melon123',
        'cherry', 'Cherry123', 'peach', 'Peach123', 'pear', 'Pear123',
        'doctor', 'Doctor123', 'nurse', 'Nurse123', 'teacher', 'Teacher1',
        'student', 'Student1', 'engineer', 'Engineer1', 'manager', 'Manager1',
        'director', 'Director1', 'president', 'President1', 'leader', 'Leader123',
        'champion', 'Champion1', 'winner', 'Winner123', 'player', 'Player123',
        'gamer', 'Gamer123', 'gaming', 'Gaming123', 'streamer', 'Streamer1'
    ];

    public static function validate($password, $firstName = '', $lastName = '', $email = '') {
        $errors = [];

        if (strlen($password) < 9) {
            $errors[] = 'Password must be at least 9 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[!@#$%^&*(),.?":\{\}|<>_\-+=\[\]\\\\\/;~]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&* etc.)';
        }

        $lowerPassword = strtolower($password);
        if (in_array($lowerPassword, self::$commonPasswords)) {
            $errors[] = 'This password is too common. Please choose a more unique password';
        }

        if (!empty($firstName) && stripos($password, $firstName) !== false) {
            $errors[] = 'Password cannot contain your first name';
        }

        if (!empty($lastName) && stripos($password, $lastName) !== false) {
            $errors[] = 'Password cannot contain your last name';
        }

        if (!empty($email)) {
            $emailParts = explode('@', $email);
            if (stripos($password, $emailParts[0]) !== false) {
                $errors[] = 'Password cannot contain your email address';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => self::calculateStrength($password)
        ];
    }

    public static function hash($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 2
        ]);
    }

    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }

    private static function calculateStrength($password) {
        $strength = 0;

        if (strlen($password) >= 9) $strength += 20;
        if (strlen($password) >= 12) $strength += 10;
        if (strlen($password) >= 16) $strength += 10;

        if (preg_match('/[a-z]/', $password)) $strength += 10;
        if (preg_match('/[A-Z]/', $password)) $strength += 15;
        if (preg_match('/[0-9]/', $password)) $strength += 15;
        if (preg_match('/[!@#$%^&*(),.?":\{\}|<>_\-+=\[\]\\\\\/;~]/', $password)) $strength += 20;

        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars > 8) $strength += 10;

        return min(100, $strength);
    }

    public static function getStrengthLabel($strength) {
        if ($strength < 40) return 'Weak';
        if ($strength < 60) return 'Fair';
        if ($strength < 80) return 'Good';
        return 'Strong';
    }
}
