CREATE TABLE products (
  ID INT NOT NULL AUTO_INCREMENT,
  reference VARCHAR(80) NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  product_unit VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  page_url VARCHAR(255) NOT NULL,
  updated_on DATE DEFAULT (CURDATE()),
  PRIMARY KEY (ID)
) ENGINE=InnoDB;



INSERT INTO products (ID, reference, name, description, product_unit, price, page_url, updated_on) VALUES
(1, 'K0709-04-2', 'PRECICE® PRPP-S Assay Kit', NULL, '10 mL of reaction mixture (half 96-well plate)', 515, 'convenient-assay-kits/prpp-s-assay-kit', '2025-10-09'),
(2, 'K0709-01-2', 'PRECICE® HPRT Assay Kit', NULL, '10 mL, 24 analyses (8 samples in triplicate)', 420, 'convenient-assay-kits/hprt-assay-kit', '2025-10-09'),
(4, 'K0709-05-2', 'AMP Deaminase Assay Kit', NULL, '24 analyses (10 ml reaction buffer)', 510, 'convenient-assay-kits/ampda-assay-kit', '2025-10-09'),
(6, 'K0709-06-2', 'ITP-ase Assay Kit', NULL, '10 mL of reaction mixture (half 96-well plate)', 440, 'convenient-assay-kits/itp-ase-assay-kit', '2025-10-09'),
(7, 'K0507-02', 'ADK Phosphorylation Assay Kit', NULL, '1 plate (96 assays)', 530, 'convenient-assay-kits/adk-phosphorylation-assay-kit', '2025-10-09'),
(8, 'K0307-01', 'dCK Phosphorylation Assay Kit', NULL, '1 plate (96 assays)', 530, 'convenient-assay-kits/dck-phosphorylation-assay-kit', '2025-10-09'),
(9, 'K0507-01.01', 'Adk Assay Kit', NULL, '1 plate (96 assays)', 530, 'convenient-assay-kits/adk-assay-kit', '2025-10-09'),
(11, 'E-Nov8 25', 'FMN-Reductase', NULL, '25 Units', 440, 'active-purified-enzymes/fmn-reductase', '2025-10-09'),
(12, 'E-Nov8 50', 'FMN-Reductase', NULL, '50 Units', 705, 'active-purified-enzymes/fmn-reductase', '2025-10-09'),
(13, 'E-Nov8 100', 'FMN-Reductase', NULL, '100 Units', 825, 'active-purified-enzymes/fmn-reductase', '2025-03-08'),
(14, 'K0700-003-12', 'For 12 samples (microplate reader)', NULL, '12 samples', 420, 'dietary-nucleotides-assay-kits/fish-freshness-assay-kit', '2025-06-02'),
(15, 'E-Nov5-100', 'Human Adenosine Kinase', NULL, '100 mUnits', 355, 'active-purified-enzymes/recombinant-adenosine-kinase', '2025-07-21'),
(16, 'E-Nov5-200', 'Human Adenosine Kinase', NULL, '200 mUnits', 625, 'convenient-assay-kits/adk-phosphorylation-assay-kit', '2025-11-07'),
(19, 'E-Nov3-500', 'Human Deoxycytidine Kinase', NULL, '500 mUnits', 295, 'active-purified-enzymes/recombinant-deoxycytidine-kinase', '2025-07-21'),
(20, 'E-Nov3-2000', 'Human Deoxycytidine Kinase', NULL, '2 Units', 945, 'active-purified-enzymes/recombinant-deoxycytidine-kinase', '2025-07-21'),
(24, 'S1200-04', 'HPLC-UV analysis for full spectra of cellular nucleotides (bases, nucleosides, NMP, NDP and NTP) in cell extracts.', NULL, '', 350, '/analytical-services/cellular-nucleotides-analysis', '2025-09-29'),
(30, 'E-Nov6-50', 'Human cytosolic 5\'-nucleotidase II', NULL, '50 mUnits', 350, 'active-purified-enzymes/cn-ii', '2025-10-09'),
(31, 'E-Nov6-100', 'Human cytosolic 5\'-nucleotidase II', NULL, '100 mUnits', 560, 'active-purified-enzymes/cn-ii', '2025-10-09'),
(32, '0700-06-12', 'PRECICE® Fishmeal IMP Assay Kit', NULL, '(microplate reader, for 12 samples) including calibration curve with IMP', 190, 'freshness-assay-kits/fishmeal-imp-assay-kit', '2025-11-25'),
(33, '0700-06-10', 'PRECICE® Fishmeal IMP Assay Kit', NULL, '(spectrophotometer, for 10 samples)', 250, 'freshness-assay-kits/fishmeal-imp-assay-kit', '2025-11-25'),
(38, 'E-Nov1-100', 'Human IMPDH Type 2', NULL, '100mU', 295, 'active-purified-enzymes/human-recombinant-impdh', '2025-12-08'),
(39, 'E-Nov1-250', 'Human IMPDH Type 2', NULL, '250mU', 550, 'active-purified-enzymes/human-recombinant-impdh', '2025-12-08'),
(40, 'Screening E-Nov1', 'Human Recombinant IMPDH2 Screening Service', NULL, 'IC50 determination for new compounds, performed in duplicate, with mycophenolic acid as the positive control.', 590, 'active-purified-enzymes/human-recombinant-impdh', '2025-12-08');


ALTER TABLE `products`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;