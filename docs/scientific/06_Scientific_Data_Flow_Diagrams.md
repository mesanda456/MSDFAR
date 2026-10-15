# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Data Flow Diagrams (DFD)

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-DFD-06
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Logical and physical data flow diagrams (Level 0 Context, Level 1 Subsystem, and Level 2 Functional Decompositions) exclusively mapping the Scientific Sampling and Enumeration Subsystem.

---

## 1. DFD Level 0: Context Diagram (Scientific Subsystem Boundary)

The Context Diagram establishes the operational boundaries of the Scientific Sampling Subsystem and its data exchanges with field officers, regional regulators, national research bodies, and international treaty secretariats:

```mermaid
graph TD
    SDO[Statistical Development Officer / Enumerator]
    FI[Fisheries Inspector / FI]
    AD[Assistant Director / AD]
    NARA[National Aquatic Resources Agency - NARA]
    IOTC[Indian Ocean Tuna Commission - IOTC]

    SCI_SYS((DFAR Scientific<br/>Sampling Subsystem))

    SDO -->|1. Daily Feasibility Status & Obstruction Reasons| SCI_SYS
    SDO -->|2. Raw Catch Sheets, Vessel Biometrics & Costs| SCI_SYS
    SCI_SYS -->|3. Data Validation Errors & Submission Receipts| SDO

    SCI_SYS -->|4. Field Sampling Queue for Verification| FI
    FI -->|5. Inspection Approvals / Correction Directives| SCI_SYS

    SCI_SYS -->|6. Regional Catch Trends & Raising Factor Summaries| AD
    AD -->|7. District Sampling Authorizations| SCI_SYS

    SCI_SYS -->|8. Anonymized Catch & Length Datasets| NARA
    NARA -->|9. Stock Assessment & MSY Recommendations| SCI_SYS

    SCI_SYS -->|10. Form 1BC Catch & Effort Nominal Data| IOTC
```

---

## 2. DFD Level 1: Subsystem Operational Data Flows

The Level 1 diagram decomposes the internal data transformation processes across the 10 core scientific data stores:

```mermaid
graph TD
    subgraph Data_Stores [Physical InnoDB Stores]
        D1[(scientific_enumeration_request)]
        D2[(scientific_data)]
        D3[(scientific_fleet_data)]
        D4[(scientific_sampling_data)]
        D5[(scientific_sampling_boat_gear_data)]
        D6[(scientific_sampling_gear_data)]
        D7[(scientific_sampling_catch_data)]
        D8[(scientific_sampling_length_details)]
        D9[(scientific_sampling_operation_cost)]
        D10[(scientific_sampling_operation_utilization)]
    end

    SDO[Field Enumerator] -->|1. Check Landing Site Feasibility| P1[1.0 Feasibility Assessment]
    P1 -->|can_continue, reson| D1

    SDO -->|2. Start Enumeration Session| P2[2.0 Session Header Ingress]
    P2 -->|district, division, site, timestamps| D2

    SDO -->|3. Tally Active Fleet| P3[3.0 Fleet Activity Census]
    P3 -->|boat_type, gear_type, no_of_boats| D3

    SDO -->|4. Select Representative Craft| P4[4.0 Sampled Vessel Ingress]
    P4 -->|boat_number, MEA plate| D4

    SDO -->|5. Capture Voyage & Gears| P5[5.0 Voyage & Effort Profiling]
    P5 -->|HP, days, hours, weather| D5
    P5 -->|depth, soaking time, hooks| D6

    SDO -->|6. Weigh Landed Catch| P6[6.0 Catch Composition Ingress]
    P6 -->|species, weight, weight_code| D7

    SDO -->|7. Measure Individual Fish| P7[7.0 Morphometric Ingress]
    P7 -->|length, length_type FL/TL, mass| D8

    SDO -->|8. Interview Skipper for Financials| P8[8.0 Economics & Post-Harvest Ingress]
    P8 -->|fuel, ice, bait, crew cost| D9
    P8 -->|export, local, dried, discard| D10

    FI[Fisheries Inspector] -->|9. Review Pending Sessions| P9[9.0 Multi-Tier Verification]
    D2 <-->|approval_stage, inspected_by| P9

    NARA[NARA Researchers] -->|10. Query Stock Datasets| P10[10.0 Stock Analytics & Export]
    D7 & D8 & D3 --> P10
```

---

## 3. DFD Level 2: Detailed Catch & Biometrics Ingress Flow

```mermaid
graph LR
    subgraph Input_Stage
        FISH_LOT[Landed Fish Sort on Beach]
        CALIPER[Measuring Board / Calipers]
        SCALE[Digital Crane Scale / Basket Scale]
    end

    subgraph Process_Stage
        P61[6.1 Identify Species Code]
        P62[6.2 Record Bulk Catch Weight]
        P71[7.1 Take Systematic Sub-sample]
        P72[7.2 Measure Fork Length FL / Total Length TL]
        P73[7.3 Record Specimen Mass]
    end

    subgraph Store_Stage
        DS_CATCH[(scientific_sampling_catch_data)]
        DS_LEN[(scientific_sampling_length_details)]
    end

    FISH_LOT --> P61
    SCALE --> P62
    P61 & P62 -->|craft_id, specie, weight, gear| DS_CATCH

    P61 --> P71
    P71 --> CALIPER
    CALIPER --> P72
    P71 --> P73
    P72 & P73 -->|sampling_data_id, specie, length, length_type| DS_LEN
```
