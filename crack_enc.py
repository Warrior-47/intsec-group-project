"""
The point of this script is to crack the encryption used in the challenge. The encryption is a simple XOR cipher
with a byte-wise reversing of the cypher text for obfuscation. The script uses a known plaintext attack to recover
the key used to encrypt the flag. The key is then used to decrypt the encrypted file to retrive the flag.

There are two modes of operation:
    1. Full decrypt: This mode requires the user to provide the starting value of the key, the cypher text and the known
    plaintext. The script then calculates the key and decrypts the file. This mode is useful when the key size is known.
    This is used after decrypting parts of the file using the second mode.

    2. Partial decrypt: This mode requires no user input. If the encrypted file exists, the script will decrypt it
    using a known plaintext attack with the flag's starting value. The decrypted file is saved in 'd_files'. The
    script then tries key sizes from 1 to 15, saving each decrypted file in the same directory with the key size in
    the filename. This helps determine the key size and the known plaintext for full decrypt mode.
"""

import os

def reverse_bytes(data: bytearray):
    """Reverses the bytes in a bytearray

    Args:
        data (bytearray): bytearray to reverse bytes

    Returns:
        bytearray: bytearray with reversed bytes
    """
    return data[::-1]

def decrypt(cypher: bytes, key: bytearray):
    """Decrypts a cypher text using a key

    Args:
        cypher (bytes): cypher text to decrypt
        key (bytearray): key to use for decryption

    Returns:
        bytearray: decrypted plain text
    """
    key_len = len(key)
    plain = bytearray()
    for i in range(0, len(cypher), key_len):
        chunk = reverse_bytes(cypher[i:i+key_len])
        plain_chunk = [ chunk[i] ^ key[i] for i in range(min(key_len, len(chunk))) ]
        plain.extend(plain_chunk)
    return plain

def known_plaintext_attack(cypher_text: bytearray, known_plaintext: bytearray, key_size: int):
    """Performs a known plaintext attack to recover the key used to encrypt the flag

    Args:
        cypher_text (bytearray): cypher text to break
        known_plaintext (bytearray): known plaintext to use for attack
        key_size (int): size of the key

    Returns:
        bytearray: potential key used to encrypt the flag
    """
    partially_known_cyphertext = reverse_bytes(cypher_text[:key_size])
    known_plaintext_length = len(known_plaintext)

    if key_size <= known_plaintext_length:
        key = bytearray([partially_known_cyphertext[i] ^ known_plaintext[i] for i in range(key_size)])
    
    else:
        key = bytearray(key_size)
        key[:known_plaintext_length] = bytearray([partially_known_cyphertext[i] ^ known_plaintext[i] for i in range(known_plaintext_length)])

    return key


if __name__ == '__main__':
    full_d = input("Full decrypt? (y/n): ")

    if full_d == 'n':
        known_plaintext = b'flag_8 is'

        with open('encrypted', 'rb') as f:
            cypher_text = f.read()
        
        for i in range(1, 15):
            print(f"Trying with key size: {i}")
            key = known_plaintext_attack(cypher_text, known_plaintext, i)

            print(f'Using key {key} to decrypt.\n')
            plaintext = decrypt(cypher_text, key)

            os.makedirs('d_files', exist_ok=True)
            with open(f'd_files/decrypted_{i}', 'wb') as f:
                f.write(plaintext)
    
    elif full_d == 'y':
        key_start = input("key starting value: ")
        cypher = input("cypher text: ")
        plain = input("known plaintext: ")

        key_start = bytearray(key_start, 'utf-8')
        cypher = bytearray(cypher, 'utf-8')
        plain = bytearray(plain, 'utf-8')

        key_end = bytearray([ cypher[i] ^ plain[i] for i in range(len(plain)) ])

        key = key_start + key_end

        with open('encrypted', 'rb') as f:
            cypher_text = f.read()

        print(f'Using key {key} to decrypt.\n')
        plaintext = decrypt(cypher_text, key)

        os.makedirs('d_files', exist_ok=True)
        with open(f'd_files/final_decrypted', 'wb') as f:
            f.write(plaintext)
    
    else:
        print("Invalid input. Exiting...")
        exit(1)


