//Tianxin Zhou zhou631@usc.edu
import java.io.IOException;
import java.util.StringTokenizer;
import java.util.HashMap;
import java.util.Map;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndex {

  public static class InvertedIndexMapper
       extends Mapper<Object, Text, Text, Text>{

    private Text docID = new Text();
    private Text word = new Text();

    public void map(Object key, Text value, Context context
                    ) throws IOException, InterruptedException {
      //Tokenize the file
      StringTokenizer itr = new StringTokenizer(value.toString());

      //Get Doc ID here
      String dummyid = itr.nextToken();
      docID.set(dummyid);
      //Preprocessing here
      String dummyString  = value.toString();
      dummyString = dummyString.toLowerCase();
      dummyString = dummyString.replaceAll("[^a-z]", " ");
      itr = new StringTokenizer(dummyString);
      //Input every word
      while (itr.hasMoreTokens()) {
        word.set(itr.nextToken());
        context.write(word, docID);
      }
    }


  }

  public static class InvertedIndexReducer
       extends Reducer<Text,Text,Text,Text> {

    public void reduce(Text key, Iterable <Text> values,
                       Context context
                       ) throws IOException, InterruptedException {

      HashMap<String, Integer> map = new HashMap<String, Integer>();
      StringBuilder result = new StringBuilder();
      for (Text val : values)
      {
        if(!map.containsKey(val.toString()))
        {
          map.put(val.toString(),1);
        }
        else
        {
          map.put(val.toString(), map.get(val.toString()) + 1);
        }
      }
      for(String id: map.keySet())
      {
        result.append(id.toString());
        result.append(":");
        result.append(map.get(id).toString());
        result.append(" ");
      }

      context.write(key, new Text(result.toString()));
    }
  }


  public static void main(String[] args) throws Exception {
    Configuration conf = new Configuration();
    Job job = Job.getInstance(conf, "InvertedIndex");
    job.setJarByClass(InvertedIndex.class);
    job.setMapperClass(InvertedIndexMapper.class);
    //job.setCombinerClass(IntSumReducer.class);
    job.setReducerClass(InvertedIndexReducer.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}
