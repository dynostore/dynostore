from zfec import Encoder, Decoder
from zfec import filefec
import os
import math

def encode_file(filepath, k, n, output_dir, file_size):
    #with open(filepath, 'rb') as f:
    #    data = f.read()

    if file_size == 0:
        raise ValueError("Input file is empty")

    with open(filepath, 'rb') as f:
        res = filefec.encode_to_files(f, file_size, output_dir,
                                        os.path.basename(filepath), k,
                                        n, overwrite=True)
        print("CHUNKIN",res,flush=True)

def decode_file(fragments_paths, fragment_indices, output_file, k, n, original_length):
    """
    Decode a file from ZFEC shares.

    :param fragments_paths: list of paths to share files (at least k of them)
    :param fragment_indices: corresponding list of fragment indices (e.g., [0, 1, 3, 5])
    :param output_file: path to write the recovered file
    :param k: number of original blocks
    :param n: total number of fragments
    :param original_length: original file size (before padding)
    """
    if len(fragments_paths) < k or len(fragment_indices) < k:
        raise ValueError("At least k fragments are required to decode.")

    # Read the share contents
    """blocks = []
    for path in fragments_paths:
        with open(path, 'rb') as f:
            blocks.append(f.read())

    # Decode
    decoder = Decoder(k, n)
    recovered_blocks = decoder.decode(blocks, fragmnt_indices)

    # Join blocks and remove padding
    recovered_data = b''.join(recovered_blocks)
    recovered_data = recovered_data[:original_length]  # remove any padding

    # Write the reconstructed file
    with open(output_file, 'wb') as f:
        f.write(recovered_data)

    print(f"[DECODE] File reconstructed at: {output_file}")"""

    # Read the share contents
    blocks = []
    with open(output_file, 'wb') as out:
        sharefs = []
        for path in fragments_paths:
            sharefs.append(open(path, 'rb'))
        
        filefec.decode_from_files(out, sharefs)
