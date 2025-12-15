import pandas as pd

# Load CSV files from storage directory
barcodes = pd.read_csv('storage/barcodes.csv')
videos = pd.read_csv('storage/videos.csv')

# Merge barcodes into videos based on filename
merged = videos.merge(barcodes[['filename', 'barcode']], on='filename', how='left', suffixes=('', '_from_barcodes'))

# Update barcode column (keep existing barcodes if present, else use merged barcodes)
merged['barcode'] = merged['barcode_from_barcodes'].combine_first(merged['barcode'])

# Drop temporary column
merged = merged.drop(columns=['barcode_from_barcodes'])

# Save the updated videos CSV
merged.to_csv('storage/videos_updated.csv', index=False)
print("Merged CSV saved as storage/videos_updated.csv")