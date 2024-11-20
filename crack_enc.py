import os

def reverse_bytes(data):
    return data[::-1]

def decrypt(cypher, key):
    key_len = len(key)
    plain = bytearray()
    for i in range(0, len(cypher), key_len):
        chunk = reverse_bytes(cypher[i:i+key_len])
        plain_chunk = [ chunk[i] ^ key[i] for i in range(min(key_len, len(chunk))) ]
        plain.extend(plain_chunk)
    return plain

def known_plaintext_attack(cypher_text: bytearray, known_plaintext: bytearray, key_size: int):
    partially_known_cyphertext = reverse_bytes(cypher_text[:key_size])
    known_plaintext_length = len(known_plaintext)

    if key_size <= known_plaintext_length:
        key = bytearray([partially_known_cyphertext[i] ^ known_plaintext[i] for i in range(key_size)])
    
    else:
        key = bytearray(key_size)
        key[:known_plaintext_length] = bytearray([partially_known_cyphertext[i] ^ known_plaintext[i] for i in range(known_plaintext_length)])

    return key

def bruteforce(idx_to_brute: int, key: bytearray, cypher_text: bytearray):
    if idx_to_brute == 1:
        for byte_val in range(128):
            key[-idx_to_brute] = byte_val
            plaintext = decrypt(cypher_text, key)
            if b'algorithm' in plaintext:
                print(f"Encryption cracked with {key}")
                with open('d_files/decrypted_brute', 'wb') as f:
                    f.write(plaintext)
                return True
        return False
    
    next_brute_idx = idx_to_brute - 1
    for byte_val in range(128):
        key[-idx_to_brute] = byte_val
        found = bruteforce(next_brute_idx, key, cypher_text)
        if found:
            return True
    
    return False


if __name__ == '__main__':
    known_plaintext = b'flag_'

    with open('encrypted', 'rb') as f:
        cypher_text = f.read()
    
    for i in range(1, 15):
        print(f"Trying with key size: {i}")
        key = known_plaintext_attack(cypher_text, known_plaintext, i)

        print(f'Using key {key} to decrypt.')
        plaintext = decrypt(cypher_text, key)

        os.makedirs('d_files', exist_ok=True)
        with open(f'd_files/decrypted_{i}', 'wb') as f:
            f.write(plaintext)