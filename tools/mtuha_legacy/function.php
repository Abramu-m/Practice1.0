<?php


require('../connect.php');

$query = mysqli_query($conn, $query = "
	 SELECT * from hospital 
	");
$row = mysqli_fetch_array($query);

$queryrufaam = mysqli_query($conn, $query = "
SELECT COUNT(CASE WHEN DATEDIFF(v.createdon,p.age)<30 THEN 1 END) 'below30', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>30 and DATEDIFF(V.createdon,p.age)<365)THEN 1 END) 'below1', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>365 and DATEDIFF(V.createdon,P.age)<1825) THEN 1 END) 'below5', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>1825 and DATEDIFF(V.createdon,P.age)<21900) THEN 1 END) 'below60', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>21900 and DATEDIFF(V.createdon,P.age)<1000000) THEN 1 END) 'above60' FROM `cons_ref` as r 
INNER JOIN pat_visit AS v on v.v_id=r.visit 
INNER JOIN patients as p on p.pat_id=v.pat_id 
where MONTH(v.createdon)='$month' and p.gender=1
	");
$rufaam = mysqli_fetch_array($queryrufaam);

$queryrufaaf = mysqli_query($conn, $query = "
SELECT COUNT(CASE WHEN DATEDIFF(v.createdon,p.age)<30 THEN 1 END) 'below30', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>30 and DATEDIFF(V.createdon,p.age)<365)THEN 1 END) 'below1', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>365 and DATEDIFF(V.createdon,P.age)<1825) THEN 1 END) 'below5', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>1825 and DATEDIFF(V.createdon,P.age)<21900) THEN 1 END) 'below60', COUNT(CASE WHEN (DATEDIFF(v.createdon,p.age)>21900 and DATEDIFF(V.createdon,P.age)<1000000) THEN 1 END) 'above60' FROM `cons_ref` as r 
INNER JOIN pat_visit AS v on v.v_id=r.visit 
INNER JOIN patients as p on p.pat_id=v.pat_id 
where MONTH(v.createdon)='$month' and p.gender=2
	");
$rufaaf = mysqli_fetch_array($queryrufaaf);

$query1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female 
FROM patients as p WHERE DATEDIFF(p.createdon,p.age) <= 30 and YEAR(createdon)='$year' and MONTH(createdon)='$month' 
   ");
$row1 = mysqli_fetch_array($query1);

$query11 = mysqli_query($conn, $query = "
	SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
	WHERE DATEDIFF(p.createdon,p.age) > 30 AND DATEDIFF(p.createdon,p.age) < 365 AND 
	 YEAR(createdon)='$year' and MONTH(createdon)='$month'  
   ");
$rowa1 = mysqli_fetch_array($query11);

$query111 = mysqli_query($conn, $query = "
   SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
   
   WHERE DATEDIFF(p.createdon,p.age) > 365 AND DATEDIFF(p.createdon,p.age) < 1825 AND 
    YEAR(createdon)='$year' and MONTH(createdon)='$month'  
  ");
$rowa2 = mysqli_fetch_array($query111);

$query14 = mysqli_query($conn, $query = "
  SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
  WHERE DATEDIFF(p.createdon,p.age) > 1825 AND DATEDIFF(p.createdon,p.age) < 21900  
  and YEAR(createdon)='$year' and MONTH(createdon)='$month' 
 ");
$rowa4 = mysqli_fetch_array($query14);

$query15 = mysqli_query($conn, $query = "
 SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
 WHERE DATEDIFF(p.createdon,p.age) > 21900  
 and YEAR(createdon)='$year' and MONTH(createdon)='$month'  
");
$rowa5 = mysqli_fetch_array($query15);


//2+3
$queryb = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) < 30 AND YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'

");
$rowb = mysqli_fetch_array($queryb);

$queryb1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 30 AND DATEDIFF(v.createdon,p.age) < 365 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'

");
$rowb1 = mysqli_fetch_array($queryb1);

$queryb2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 365 AND DATEDIFF(v.createdon,p.age) < 1825 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'

");
$rowb2 = mysqli_fetch_array($queryb2);

$queryb4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 1825 AND DATEDIFF(v.createdon,p.age) < 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'

");
$rowb4 = mysqli_fetch_array($queryb4);

$queryb5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'

");
$rowb5 = mysqli_fetch_array($queryb5);

//new visit
$queryd = mysqli_query($conn, $query = "

SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female 
FROM patients as p WHERE DATEDIFF(p.createdon,p.age) <= 30 and YEAR(createdon)='$year' and MONTH(createdon)='$month' 
     
");
$rowd = mysqli_fetch_array($queryd);

$queryd1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
WHERE DATEDIFF(p.createdon,p.age) > 30 AND DATEDIFF(p.createdon,p.age) < 365 AND 
 YEAR(createdon)='$year' and MONTH(createdon)='$month'  
");
$rowd1 = mysqli_fetch_array($queryd1);

$queryd2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p  
WHERE DATEDIFF(p.createdon,p.age) > 365 AND DATEDIFF(p.createdon,p.age) < 1825 AND 
 YEAR(createdon)='$year' and MONTH(createdon)='$month'  
");
$rowd2 = mysqli_fetch_array($queryd2);

$queryd4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
WHERE DATEDIFF(p.createdon,p.age) > 1825 AND DATEDIFF(p.createdon,p.age) < 21900  
and YEAR(createdon)='$year' and MONTH(createdon)='$month'
");
$rowd4 = mysqli_fetch_array($queryd4);

$queryd5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
WHERE DATEDIFF(p.createdon,p.age) > 21900  
and YEAR(createdon)='$year' and MONTH(createdon)='$month' 
");
$rowd5 = mysqli_fetch_array($queryd5);



//REVISIT
$queryc = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 0 AND DATEDIFF(v.createdon,p.age) <= 30 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pat_id IN (SELECT pat_id FROM pat_visit WHERE MONTH(createdon)<='$month' AND YEAR(createdon)<='$year' GROUP BY pat_id HAVING COUNT(*) >1)
 ");
$rowc = mysqli_fetch_array($queryc);

$queryc1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 30 AND DATEDIFF(v.createdon,p.age) < 365 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pat_id IN (SELECT pat_id FROM pat_visit WHERE MONTH(createdon)<='$month' AND YEAR(createdon)<='$year' GROUP BY pat_id HAVING COUNT(*) >1)
");
$rowc1 = mysqli_fetch_array($queryc1);

$queryc2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 365 AND DATEDIFF(v.createdon,p.age) < 1825 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pat_id IN (SELECT pat_id FROM pat_visit WHERE MONTH(createdon)<='$month' AND YEAR(createdon)<='$year' GROUP BY pat_id HAVING COUNT(*) >1)
");
$rowc2 = mysqli_fetch_array($queryc2);

$queryc4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 1825 AND DATEDIFF(v.createdon,p.age) < 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pat_id IN (SELECT pat_id FROM pat_visit WHERE MONTH(createdon)<='$month' AND YEAR(createdon)<='$year' GROUP BY pat_id HAVING COUNT(*) >1)

");
$rowc4 = mysqli_fetch_array($queryc4);

$queryc5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pat_id IN (SELECT pat_id FROM pat_visit WHERE MONTH(createdon)<='$month' AND YEAR(createdon)<='$year' GROUP BY pat_id HAVING COUNT(*) >1)
");
$rowc5 = mysqli_fetch_array($queryc5);






//NHIF, 2
$querye = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) <= 30 AND YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pv_cat_id = 2
");
$rowe = mysqli_fetch_array($querye);

$querye1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 30 AND DATEDIFF(v.createdon,p.age) < 365 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id = 2

");
$rowe1 = mysqli_fetch_array($querye1);

$querye2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 365 AND DATEDIFF(v.createdon,p.age) < 1825 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id = 2

");
$rowe2 = mysqli_fetch_array($querye2);

$querye4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 1825 AND DATEDIFF(v.createdon,p.age) < 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id = 2

");
$rowe4 = mysqli_fetch_array($querye4);

$querye5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id = 2

");
$rowe5 = mysqli_fetch_array($querye5);

//Other insurance, 3, 4, 5, 7
$queryf = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) <= 30 AND YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pv_cat_id IN (3, 4, 5, 7)
");
$rowef = mysqli_fetch_array($queryf);

$queryf1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 30 AND DATEDIFF(v.createdon,p.age) < 365 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (3, 4, 5, 7)
");
$rowef1 = mysqli_fetch_array($queryf1);

$queryf2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 365 AND DATEDIFF(v.createdon,p.age) < 1825 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (3, 4, 5, 7)
");
$rowef2 = mysqli_fetch_array($queryf2);

$queryf4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 1825 AND DATEDIFF(v.createdon,p.age) < 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (3, 4, 5, 7)
");
$rowef4 = mysqli_fetch_array($queryf4);

$queryf5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (3, 4, 5, 7)
");
$rowef5 = mysqli_fetch_array($queryf5);



//cash
$queryf = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) <= 30 AND YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
AND v.pv_cat_id IN (1, 8, 9, 10, 11)
");
$rowf = mysqli_fetch_array($queryf);

$queryf1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 30 AND DATEDIFF(v.createdon,p.age) < 365 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (1, 8, 9, 10, 11)

");
$rowf1 = mysqli_fetch_array($queryf1);

$queryf2 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 365 AND DATEDIFF(v.createdon,p.age) < 1825 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (1, 8, 9, 10, 11)

");
$rowf2 = mysqli_fetch_array($queryf2);

$queryf4 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' 
WHERE DATEDIFF(v.createdon,p.age) > 1825 AND DATEDIFF(v.createdon,p.age) < 21900 AND  YEAR(v.createdon)='$year' AND MONTH(v.createdon)='$month'
 AND v.pv_cat_id IN (1, 8, 9, 10, 11)

");
$rowf4 = mysqli_fetch_array($queryf4);

$queryf5 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p
INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year'  AND MONTH(v.createdon)='$month'
WHERE DATEDIFF(v.createdon,p.age) > 21900 
 AND v.pv_cat_id IN (1, 8, 9, 10, 11)

");
$rowf5 = mysqli_fetch_array($queryf5);



//blood slide for malaria parasite case code 28
$queryg = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month'
INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=28 and r.preports<>'Negative'
 WHERE DATEDIFF(l.createdon,p.age) <= 30  
 ");
$rowg = mysqli_fetch_array($queryg);

$queryg1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=28 and r.preports<>'Negative'
 WHERE DATEDIFF(l.createdon,p.age)  > 30 AND DATEDIFF(l.createdon,p.age) < 365 
 ");
$rowg1 = mysqli_fetch_array($queryg1);

$queryg2 = mysqli_query($conn, $query = "
 SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
 INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
 INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=28 and r.preports<>'Negative'
  WHERE DATEDIFF(l.createdon,p.age)  > 365 AND DATEDIFF(l.createdon,p.age) < 1825 
  ");
$rowg2 = mysqli_fetch_array($queryg2);


$queryg4 = mysqli_query($conn, $query = "
  SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
  INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
  INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=28 and r.preports<>'Negative'
   WHERE DATEDIFF(l.createdon,p.age)  > 1825 AND DATEDIFF(l.createdon,p.age) < 21900 
   ");
$rowg4 = mysqli_fetch_array($queryg4);

$queryg5 = mysqli_query($conn, $query = "
   SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
   INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
   INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=28 and r.preports<>'Negative'
	WHERE DATEDIFF(l.createdon,p.age)  > 21900
	");
$rowg5 = mysqli_fetch_array($queryg5);

//blood slide for malaria MRDT    29
$queryh = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month'
INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=29 and r.preports<>'Negative'
 WHERE DATEDIFF(l.createdon,p.age) <= 30  
 ");
$rowh = mysqli_fetch_array($queryh);

$queryh1 = mysqli_query($conn, $query = "
SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=29 and r.preports<>'Negative'
 WHERE DATEDIFF(l.createdon,p.age)  > 30 AND DATEDIFF(l.createdon,p.age) < 365 
 ");
$rowh1 = mysqli_fetch_array($queryh1);

$queryh2 = mysqli_query($conn, $query = "
 SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
 INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
 INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=29 and r.preports<>'Negative'
  WHERE DATEDIFF(l.createdon,p.age)  > 365 AND DATEDIFF(l.createdon,p.age) < 1825 
  ");
$rowh2 = mysqli_fetch_array($queryh2);


$queryh4 = mysqli_query($conn, $query = "
  SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
  INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
  INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=29 and r.preports<>'Negative'
   WHERE DATEDIFF(l.createdon,p.age)  > 1825 AND DATEDIFF(l.createdon,p.age) < 21900 
   ");
$rowh4 = mysqli_fetch_array($queryh4);

$queryh5 = mysqli_query($conn, $query = "
   SELECT count( case when gender='1' then 1 end ) as Male , count( case when gender='2' then 1 end ) as Female FROM patients as p 
   INNER JOIN pat_lab as l on l.pat_id=p.pat_id and YEAR(l.createdon)='$year' AND MONTH(l.createdon)='$month' 
   INNER JOIN procedures AS r on r.pl_id=l.id AND l.svcode=29 and r.preports<>'Negative'
	WHERE DATEDIFF(l.createdon,p.age)  > 21900
	");
$rowh5 = mysqli_fetch_array($queryh5);


//Infections first part, 1-13
$infections1 = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 1 and id <= 13");

//Infections second part, 15-20
$infections2 = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 15 and id <= 20");

//Neoplasms 21
$neoplasm = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id = 21");

//Blood, 22-26
$blood = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 22 and id <= 26");

//Endocrine, 27-35
$endocrine = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 27 and id <= 35");

//Mental, 36-40
$mental = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 36 and id <= 40");

//Nervous, 41-42
$nervous = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 41 and id <= 42");

//Eye, 43-46
$eye = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 43 and id <= 46");

//Ear, 47-49
$ear = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 47 and id <= 49");

//Circulatory, 50-52
$circulatory = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 50 and id <= 52");

//Respiratory, 53-57
$respiratory = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 53 and id <= 57");

//Digestive, 58-67
$digestive = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 58 and id <= 67");

//Skin, 68-72
$skin = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 68 and id <= 72");

//Musculoskeletal, 73-78
$musculoskeletal = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 73 and id <= 78");

//Genitourinary, 79-87
$genitourinary = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 79 and id <= 87");

//Pregnancy, 88-95
$pregnancy = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 88 and id <= 95");

//Perinatal, 96-99
$perinatal = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 96 and id <= 99");

//Congenital, 100-101
$congenital = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 100 and id <= 101");

//Injury, 102-111
$injury = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 102 and id <= 111");

//Symptoms, 0
$symptoms = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id = 0");

//External, 112-115
$external = mysqli_query($conn, $query = "SELECT  * from new_mtuha_diagnoses where id >= 112 and id <= 115");


function diagnoses($gender, $year, $month, $category, $date1, $date2)
{
    require('../connect.php');

   $querydi = mysqli_query($conn, $query = "
	SELECT count( case when gender='$gender' then 1 end ) as data FROM patients as p
	INNER JOIN pat_visit as v on v.pat_id=p.pat_id and YEAR(v.createdon)='$year' and MONTH(v.createdon)='$month' 
	INNER JOIN cons_icd as ci ON ci.v_id=v.v_id
   INNER JOIN new_icd_grouping as g on g.icd=ci.icd_code and g.status = '1' and ci.icd_type='F'
	INNER JOIN new_mtuha_diagnoses as ic on ic.id=g.category and ic.id='$category'
	WHERE DATEDIFF(v.createdon,p.age) > '$date1' AND DATEDIFF(v.createdon,p.age) <= '$date2'
");
   $rowdi = mysqli_fetch_array($querydi);
   return $rowdi['data'];
}


function malariapos($gender, $year, $month, $date1, $date2, $malaria)
{
    require('../connect.php');

   $querymalaria = mysqli_query($conn, $query = "
	 select count(*) as data from pat_lab as l INNER JOIN procedures as r on l.id=r.pl_id and l.svcode ='$malaria' AND 
   MONTH(l.createdon)='$month' AND YEAR(l.createdon)='$year' INNER JOIN patients AS p on p.pat_id=l.pat_id and p.gender='$gender' 
   WHERE DATEDIFF(l.createdon,p.age) > '$date1' AND DATEDIFF(l.createdon,p.age) <='$date2' and r.preports<>'Negative'  
 ");
   $rowmalaria = mysqli_fetch_array($querymalaria);
   return $rowmalaria['data'];
}

function malarianeg($gender, $year, $month, $date1, $date2, $malaria)
{
    require('../connect.php');

   $querymalaria2 = mysqli_query($conn, $query = "
   select count(*) as data from pat_lab as l INNER JOIN procedures as r on l.id=r.pl_id and l.svcode ='$malaria' AND 
   MONTH(l.createdon)='$month' AND YEAR(l.createdon)='$year' INNER JOIN patients AS p on p.pat_id=l.pat_id and p.gender='$gender' 
   WHERE DATEDIFF(l.createdon,p.age) > '$date1' AND DATEDIFF(l.createdon,p.age) <='$date2' and r.preports='Negative' 

 ");
   $rowmalaria2 = mysqli_fetch_array($querymalaria2);
   return $rowmalaria2['data'];
}
/////**** STI Report ******///////////
$querydiagnoses_std = mysqli_query($conn, $query = " SELECT  * from new_mtuha_diagnoses where id IN(84, 85, 86, 81) ");


////////////////// TO BE CHECKED //////////////////////
$querydiagnoses_diarrhea = mysqli_query($conn, $query = "
   SELECT  * from icd_category where id IN(19, 20, 21)
	");