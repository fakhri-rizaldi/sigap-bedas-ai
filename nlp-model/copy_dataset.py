import os
import shutil

src_csv = 'dataset_aduan.csv'
dst_dir = os.path.join('nlp-model', 'data')
os.makedirs(dst_dir, exist_ok=True)

shutil.copy(src_csv, os.path.join(dst_dir, 'dataset_aduan_1200.csv'))
shutil.copy(src_csv, os.path.join(dst_dir, 'dataset_aduan.csv'))
print("Berhasil menyalin dataset ke nlp-model/data/")
