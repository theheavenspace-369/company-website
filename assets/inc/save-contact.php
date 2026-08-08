<?php
header('Content-Type: text/plain; charset=utf-8');

/**
 * Validate and sanitize a name field.
 *
 * @param string $name
 * @return string|false
 */
function validateName($name)
{
    $name = trim($name);
    if (!preg_match("/^[A-Za-z\s\-'\.]{2,100}$/", $name)) {
        return false;
    }
    return $name;
}

/**
 * Validate and sanitize an email field.
 *
 * @param string $email
 * @return string|false
 */
function validateEmail($email)
{
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Validate and sanitize an optional phone field.
 *
 * @param string $phone
 * @return string|false
 */
function validatePhone($phone)
{
    $phone = trim($phone);
    if ($phone === '') {
        return '';
    }
    if (!preg_match('/^(\+91[\-\s]?)?[6-9][0-9]{9}$/', $phone)) {
        return false;
    }
    return $phone;
}

/**
 * Sanitize a plain text field to a maximum length.
 *
 * @param string $text
 * @param int $maxLength
 * @return string
 */
function sanitizeText($text, $maxLength)
{
    $text = trim($text);
    return mb_substr($text, 0, $maxLength);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $name = validateName($_POST['name'] ?? '');
    if ($name === false) {
        throw new Exception('Please provide a valid name (letters only, at least 2 characters).');
    }

    $email = validateEmail($_POST['email'] ?? '');
    if ($email === false) {
        throw new Exception('Please provide a valid email address.');
    }

    $phone = validatePhone($_POST['phone'] ?? '');
    if ($phone === false) {
        throw new Exception('Please provide a valid phone number.');
    }

    $message = sanitizeText($_POST['message'] ?? '', 2000);
    if (mb_strlen($message) < 10) {
        throw new Exception('Please provide a more detailed message (at least 10 characters).');
    }

    $subject = sanitizeText($_POST['subject'] ?? '', 200);

    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir) && !mkdir($dataDir, 0755, true) && !is_dir($dataDir)) {
        throw new Exception('Unable to store your request right now. Please try again later.');
    }
    $dataFile = $dataDir . '/contacts.json';

    $handle = fopen($dataFile, 'c+');
    if ($handle === false) {
        throw new Exception('Unable to store your request right now. Please try again later.');
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        throw new Exception('Unable to store your request right now. Please try again later.');
    }

    $existingContent = stream_get_contents($handle);
    $entries = json_decode($existingContent, true);
    if (!is_array($entries)) {
        $entries = [];
    }

    $entries[] = [
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message,
        'submitted_at' => date('c'),
    ];

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    echo 'Thank you for contacting us. We will get back to you soon!';
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());

    $errorMessage = 'An error occurred while processing your request. Please try again later.';
    if (
        strpos($e->getMessage(), 'Please') === 0 ||
        strpos($e->getMessage(), 'Invalid') === 0
    ) {
        $errorMessage = $e->getMessage();
        http_response_code(400);
    } else {
        http_response_code(500);
    }

    echo $errorMessage;
}
